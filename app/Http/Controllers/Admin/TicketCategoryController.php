<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketCategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::withCount('tickets')->orderBy('name')->get();

        return view('admin.ticket-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        TicketCategory::create($this->validated($request));

        return back()->with('success', 'Kategori tiket ditambahkan.');
    }

    public function update(Request $request, TicketCategory $ticketCategory)
    {
        $ticketCategory->update($this->validated($request, $ticketCategory));

        return back()->with('success', 'Kategori "'.$ticketCategory->name.'" diperbarui.');
    }

    private function validated(Request $request, ?TicketCategory $category = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('ticket_categories', 'name')->ignore($category?->id)],
            'description' => 'nullable|string|max:255',
        ]);

        return $validated + [
            'is_active' => $request->boolean('is_active'),
            'requires_approval' => $request->boolean('requires_approval'),
        ];
    }
}
