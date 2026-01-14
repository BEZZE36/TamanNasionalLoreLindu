<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(12)
            ->through(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'description' => $t->description,
                'is_active' => $t->is_active,
                'variables' => $t->variables,
            ]);
        return Inertia::render('Admin/EmailTemplates/Index', ['templates' => $templates]);
    }

    public function create()
    {
        $templateTypes = EmailTemplate::TEMPLATES;
        $defaultVariables = EmailTemplate::DEFAULT_VARIABLES;
        return Inertia::render('Admin/EmailTemplates/Create', ['templateTypes' => $templateTypes, 'defaultVariables' => $defaultVariables]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:email_templates,slug',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['variables'] = EmailTemplate::DEFAULT_VARIABLES[$validated['slug']] ?? [];

        EmailTemplate::create($validated);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dibuat.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        $templateTypes = EmailTemplate::TEMPLATES;
        $defaultVariables = EmailTemplate::DEFAULT_VARIABLES;
        return Inertia::render('Admin/EmailTemplates/Edit', [
            'emailTemplate' => [
                'id' => $emailTemplate->id,
                'name' => $emailTemplate->name,
                'slug' => $emailTemplate->slug,
                'subject' => $emailTemplate->subject,
                'body' => $emailTemplate->body,
                'description' => $emailTemplate->description,
                'is_active' => $emailTemplate->is_active,
                'variables' => $emailTemplate->variables,
            ],
            'templateTypes' => $templateTypes,
            'defaultVariables' => $defaultVariables,
        ]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $emailTemplate->update($validated);

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email berhasil dihapus.');
    }

    public function preview(EmailTemplate $emailTemplate)
    {
        $preview = $emailTemplate->preview();
        return response()->json([
            'subject' => $emailTemplate->subject,
            'body' => $preview,
        ]);
    }
}
