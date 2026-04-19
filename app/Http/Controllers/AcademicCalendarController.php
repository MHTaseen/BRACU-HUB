<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AcademicEvent;

class AcademicCalendarController extends Controller
{
    public function index()
    {
        $events = AcademicEvent::orderBy('start_date')->get();
        // Later we will merge this with assignments/deadlines based on the authenticated user.
        
        return view('academic.calendar.index', compact('events'));
    }
}
