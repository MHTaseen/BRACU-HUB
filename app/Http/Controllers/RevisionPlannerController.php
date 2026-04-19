<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RevisionPlannerController extends Controller
{
    // =========================================================================
    // Flashcard definitions for common CS / Math topics (fallback if no match)
    // =========================================================================
    private const DEFINITIONS = [
        // Networking
        'protocols & layers'  => 'A protocol is a set of rules governing data exchange. The OSI model has 7 layers; TCP/IP has 4.',
        'http'                => 'HyperText Transfer Protocol — the foundation of data communication on the web. Stateless, request-response model.',
        'email'               => 'Electronic mail uses SMTP (send), POP3/IMAP (receive). Messages are routed through mail servers.',
        'dns'                 => 'Domain Name System — translates human-readable domain names (e.g. google.com) into IP addresses.',
        'tcp'                 => 'Transmission Control Protocol — connection-oriented, reliable, ordered delivery. Uses 3-way handshake.',
        'udp'                 => 'User Datagram Protocol — connectionless, faster than TCP, no delivery guarantee. Used in streaming.',
        // Programming
        'arrays'              => 'A contiguous block of memory storing elements of the same type. O(1) access by index.',
        'linked list'         => 'A sequence of nodes where each node holds data and a pointer to the next node.',
        'recursion'           => 'A function that calls itself with a smaller sub-problem until a base case is reached.',
        'sorting'             => 'Arrangement of data in order. Key algorithms: Bubble, Merge (O n log n), Quick (avg O n log n).',
        'searching'           => 'Finding an element. Linear search O(n); Binary search O(log n) on sorted arrays.',
        'tree'                => 'A hierarchical data structure with a root, branches, and leaves. BST: left < root < right.',
        'graph'               => 'A set of vertices connected by edges. Can be directed/undirected, weighted/unweighted.',
        'oop'                 => 'Object-Oriented Programming: Encapsulation, Inheritance, Polymorphism, Abstraction.',
        'pointers'            => 'A variable that stores the memory address of another variable. Core concept in C/C++.',
        // Math
        'calculus'            => 'Study of rates of change (differentiation) and accumulation (integration).',
        'matrix'              => 'A rectangular array of numbers. Key operations: addition, multiplication, determinant, inverse.',
        'probability'         => 'Measure of likelihood of an event occurring. P(A) = favourable outcomes / total outcomes.',
        'statistics'          => 'Collection, analysis, interpretation of data. Mean, median, mode, standard deviation.',
        'set theory'          => 'Study of sets — collections of distinct objects. Union, intersection, complement, subset.',
        'logic'               => 'Study of valid reasoning. Propositional logic: AND, OR, NOT, implication, equivalence.',
        'number theory'       => 'Study of integers. Covers primes, divisibility, GCD, modular arithmetic.',
        // General CS
        'os'                  => 'Operating System manages hardware resources, provides process/memory/file management.',
        'database'            => 'Organized collection of structured data. RDBMS uses tables, SQL for querying.',
        'sql'                 => 'Structured Query Language. SELECT, INSERT, UPDATE, DELETE, JOIN operations.',
        'complexity'          => 'Big-O notation describes algorithm efficiency. O(1) constant, O(n) linear, O(n²) quadratic.',
        'encryption'          => 'Converting plaintext to ciphertext. Symmetric (same key) vs Asymmetric (public/private key).',
    ];

    // =========================================================================
    // Resource link generators per tag name
    // =========================================================================
    private function resources(string $tagName, string $courseCode): array
    {
        $encoded = urlencode($tagName);
        $isCS    = stripos($courseCode, 'CSE') !== false
                || stripos($courseCode, 'CIS') !== false
                || stripos($courseCode, 'SWE') !== false;
        $isMath  = stripos($courseCode, 'MAT') !== false
                || stripos($courseCode, 'PHY') !== false
                || stripos($courseCode, 'STA') !== false;

        $resources = [
            'video'    => 'https://www.youtube.com/results?search_query=' . $encoded . '+tutorial',
            'wiki'     => 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', $tagName),
            'practice' => 'https://quizlet.com/search?query=' . $encoded,
        ];

        if ($isCS) {
            $resources['reading'] = 'https://www.geeksforgeeks.org/search/?q=' . $encoded;
            $resources['practice'] = 'https://leetcode.com/search/?q=' . $encoded;
        } elseif ($isMath) {
            $resources['reading'] = 'https://www.khanacademy.org/search?referer=%2F&page_search_query=' . $encoded;
            $resources['practice'] = 'https://www.wolframalpha.com/input?i=' . $encoded;
        } else {
            $resources['reading'] = 'https://www.coursera.org/search?query=' . $encoded;
        }

        return $resources;
    }

    // =========================================================================
    // Flashcard definition lookup (case-insensitive)
    // =========================================================================
    private function definition(string $tagName): string
    {
        $key = strtolower(trim($tagName));
        return self::DEFINITIONS[$key]
            ?? 'Click the resource links below to study this topic in depth.';
    }

    // =========================================================================
    // Performance badge
    // =========================================================================
    private function perfBadge(?float $pct): array
    {
        if ($pct === null)  return ['Not Graded', '#64748b', '—'];
        if ($pct >= 80)     return ['Excellent',   '#4ade80', '🔥'];
        if ($pct >= 60)     return ['Passed',       '#22d3ee', '✅'];
        if ($pct >= 40)     return ['Needs Work',   '#fb923c', '⚠'];
        return                     ['Critical',     '#f87171', '❌'];
    }

    // =========================================================================
    // Main controller action
    // =========================================================================
    public function index()
    {
        $student   = Auth::user();
        $studentId = $student->id;

        // 1. Enrolled sections with course + tags + assignments + attendances
        $enrolledSections = $student->enrolledSections()
            ->with([
                'course.tags',
                'assignments.submissions' => function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                },
            ])
            ->get();

        if ($enrolledSections->isEmpty()) {
            return view('academic.student.revision', [
                'courses' => collect(),
            ]);
        }

        // ── Build per-course data ──────────────────────────────────────────
        $courses = [];

        foreach ($enrolledSections as $section) {
            $course    = $section->course;
            $courseId  = $course->id;
            $now       = now();

            // ── Quizzes in this section ──────────────────────────────────
            $quizzes = $section->assignments->where('type', 'Quiz');
            $allAssignments = $section->assignments;

            // ── Identify weak quizzes (not submitted OR marks < 60%) ─────
            $weakTopics = collect(); // keyword signals from quiz titles
            $quizRows   = [];

            foreach ($quizzes as $quiz) {
                $sub = $quiz->submissions->first();
                $pct = $sub ? $sub->performancePercent() : null;

                $isWeak    = !$sub || ($pct !== null && $pct < 60);
                $isMissing = !$sub;

                [$badgeLabel, $badgeColor, $badgeIcon] = $this->perfBadge($pct);

                $quizRows[] = [
                    'quiz'        => $quiz,
                    'submission'  => $sub,
                    'pct'         => $pct,
                    'is_weak'     => $isWeak,
                    'is_missing'  => $isMissing,
                    'badge_label' => $badgeLabel,
                    'badge_color' => $badgeColor,
                    'badge_icon'  => $badgeIcon,
                    'days_ago'    => $quiz->due_date < $now
                        ? $quiz->due_date->diffInDays($now) . 'd ago'
                        : 'in ' . $now->diffInDays($quiz->due_date) . 'd',
                ];

                // Extract keyword signals from weak quiz titles
                if ($isWeak) {
                    $words = preg_split('/[\s,\-_]+/', strtolower($quiz->title));
                    foreach ($words as $w) {
                        if (strlen($w) > 3) $weakTopics->push($w);
                    }
                }
            }

            // ── Build prioritised topic list ─────────────────────────────
            $topics = [];

            foreach ($course->tags as $tag) {
                $tagLower = strtolower($tag->name);

                // Quiz weakness signal: does tag name overlap quiz title words?
                $weakSignal = $weakTopics->contains(function ($word) use ($tagLower) {
                    return str_contains($tagLower, $word) || str_contains($word, $tagLower);
                }) ? 3 : 1;

                // Urgency signal: upcoming assignments in this section
                $urgency = 1;
                foreach ($allAssignments as $asgn) {
                    if ($asgn->due_date >= $now) {
                        $daysLeft = $now->diffInDays($asgn->due_date);
                        if ($daysLeft <= 3)       $urgency = max($urgency, 4);
                        elseif ($daysLeft <= 7)   $urgency = max($urgency, 3);
                        elseif ($daysLeft <= 14)  $urgency = max($urgency, 2);
                    }
                }

                // Weight signal: highest weight assignment in section
                $maxWeight = $allAssignments->max('weight') ?? 10;

                $priorityScore = $weakSignal * $urgency * ($maxWeight / 10);

                $topics[] = [
                    'tag'            => $tag,
                    'priority_score' => $priorityScore,
                    'is_weak'        => $weakSignal > 1,
                    'urgency'        => $urgency,
                    'definition'     => $this->definition($tag->name),
                    'resources'      => $this->resources($tag->name, $course->code),
                ];
            }

            // Sort by priority descending
            usort($topics, fn($a, $b) => $b['priority_score'] <=> $a['priority_score']);

            // ── Aggregate quiz stats ─────────────────────────────────────
            $gradedQuizzes = collect($quizRows)->filter(fn($r) => $r['pct'] !== null);
            $avgPct = $gradedQuizzes->count()
                ? round($gradedQuizzes->avg('pct'), 1)
                : null;

            $courses[$courseId] = [
                'course'       => $course,
                'section'      => $section,
                'topics'       => $topics,
                'quiz_rows'    => $quizRows,
                'avg_pct'      => $avgPct,
                'weak_count'   => collect($quizRows)->where('is_weak', true)->count(),
            ];
        }

        return view('academic.student.revision', [
            'courses' => collect($courses),
        ]);
    }


    // =========================================================================
    // Wikipedia REST API — live definition lookup
    // Endpoint: GET /wiki-summary?topic={topic}
    // Cached for 7 days per topic to avoid redundant API calls.
    // =========================================================================
    public function wikiSummary(Request $request)
    {
        $topic = trim($request->query('topic', ''));

        if (empty($topic)) {
            return response()->json(['summary' => null, 'source' => 'none']);
        }

        // Normalise topic into a Wikipedia-friendly title slug
        $slug     = str_replace(' ', '_', ucwords(strtolower($topic)));
        $cacheKey = 'wiki_summary_' . md5($slug);

        $summary = Cache::remember($cacheKey, now()->addDays(7), function () use ($slug, $topic) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$slug}");

                if ($response->successful()) {
                    $data = $response->json();

                    // Wikipedia returns a 'type' of 'disambiguation' for ambiguous terms
                    if (($data['type'] ?? '') === 'disambiguation') {
                        return null; // fall through to fallback
                    }

                    $extract = $data['extract'] ?? null;

                    // Cap to ~3 sentences for readability on the flashcard
                    if ($extract) {
                        $sentences = preg_split('/(?<=[.!?])\s+/', $extract, -1, PREG_SPLIT_NO_EMPTY);
                        $extract   = implode(' ', array_slice($sentences, 0, 3));
                    }

                    return $extract ?: null;
                }

                return null;
            } catch (\Throwable $e) {
                return null; // network error — fall through to fallback
            }
        });

        return response()->json([
            'summary'    => $summary,
            'source'     => $summary ? 'wikipedia' : 'fallback',
            'wiki_url'   => $summary
                ? 'https://en.wikipedia.org/wiki/' . str_replace(' ', '_', ucwords(strtolower($topic)))
                : null,
        ]);
    }
}
