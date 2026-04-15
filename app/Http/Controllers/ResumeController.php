<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResumeController extends Controller
{
    public function index(): Response
    {
        $resumes = Resume::with('template')
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn ($resume) => [
                'id' => $resume->id,
                'title' => $resume->title,
                'template' => $resume->template ? [
                    'name' => $resume->template->name,
                    'component_name' => $resume->template->component_name,
                ] : null,
                'is_downloaded' => $resume->is_downloaded,
                'updated_at' => $resume->updated_at->diffForHumans(),
            ]);

        return Inertia::render('Resume/Index', [
            'resumes' => $resumes,
        ]);
    }

    public function create(): Response
    {
        $templates = Template::active()->get(['id', 'name', 'slug', 'thumbnail_url', 'component_name', 'config']);

        return Inertia::render('Resume/Create', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'template_id' => 'required|exists:templates,id',
        ]);

        $template = Template::findOrFail($validated['template_id']);

        $resume = Resume::create([
            'user_id' => Auth::id(),
            'template_id' => $validated['template_id'],
            'title' => $validated['title'],
            'data' => [
                'personalData' => [
                    'firstName'      => explode(' ', Auth::user()->name)[0] ?? '',
                    'lastName'       => implode(' ', array_slice(explode(' ', Auth::user()->name), 1)) ?: '',
                    'title'          => Auth::user()->professional_title ?? '',
                    'email'          => Auth::user()->email,
                    'phone'          => Auth::user()->phone ?? '',
                    'location'       => Auth::user()->location ?? '',
                    'country'        => 'Brasil',
                    'website'        => Auth::user()->website_url ?? '',
                    'photo'          => Auth::user()->avatar_url ?? '',
                    'dateOfBirth'    => Auth::user()->date_of_birth
                                            ? Auth::user()->date_of_birth->format('Y-m-d')
                                            : '',
                    'nationality'    => Auth::user()->nationality ?? '',
                    'drivingLicense' => '',
                    'linkedIn'       => Auth::user()->linkedin_url ?? '',
                ],
                'summary' => '',
                'workHistory' => [],
                'education' => [],
                'skills' => [],
                'links' => [],
                'additional' => [
                    'languages' => [],
                    'certifications' => [],
                    'courses' => [],
                    'hobbies' => [],
                ],
            ],
            'customization' => [
                'color' => data_get($template->config, 'colors.0', '#2563eb'),
                'font' => data_get($template->config, 'fonts.0', 'Inter'),
                'fontSize' => 'md',
                'spacing' => 'normal',
                'layout' => 'A4',
                'showSkillLevels' => true,
            ],
            'current_version' => 1,
            'is_downloaded' => false,
        ]);

        return redirect()->route('resumes.edit', $resume);
    }

    public function edit(Resume $resume): Response
    {
        abort_if($resume->user_id !== Auth::id(), 403);

        $templates = Template::active()->get(['id', 'name', 'slug', 'thumbnail_url', 'component_name', 'config']);

        return Inertia::render('Resume/Edit', [
            'resume' => $resume,
            'templates' => $templates,
        ]);
    }

    public function update(Request $request, Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'template_id' => 'sometimes|exists:templates,id',
            'data' => 'sometimes|array',
            'customization' => 'sometimes|array',
        ]);

        $resume->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'saved' => true,
                'updated_at' => $resume->updated_at->format('H:i:s'),
            ]);
        }

        return back();
    }

    public function destroy(Resume $resume)
    {
        abort_if($resume->user_id !== Auth::id(), 403);

        $resume->delete();

        return redirect()->route('resumes.index');
    }
}
