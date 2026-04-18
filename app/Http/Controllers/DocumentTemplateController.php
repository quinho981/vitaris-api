<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        return DocumentTemplate::orderBy('id')->get();
    }

    public function listTemplatesWithUserDocumentsCount(Request $request)
    {
        $userId = Auth::id();
        $perPage = $request->get('per_page', 10);

        $baseQuery = $this->baseTemplatesQuery($userId);

        if ($request->search) {
            $baseQuery->where('name', 'ilike', "%{$request->search}%");
        }

        if ($request->category && $request->category !== 'all') {
            $baseQuery->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'ilike', "%{$request->category}%");
            });
        }

        $top = Cache::remember("top_templates_user_{$userId}", 300, function () use ($userId) {
            return $this->baseTemplatesQuery($userId)
                ->whereHas('documents', function ($docQuery) use ($userId) {
                    $docQuery->withTrashed()
                        ->whereHas('transcript', function ($q) use ($userId) {
                            $q->withTrashed()
                                ->where('user_id', $userId);
                        });
                })
                ->orderByDesc('total')
                ->limit(3)
                ->get();
        });

        $paginated = $baseQuery
            ->orderBy('name')
            ->paginate($perPage);

        return [
            'top' => $top,
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
            ]
        ];
    }

    private function baseTemplatesQuery($userId)
    {
        return DocumentTemplate::query()
            ->select('id', 'name', 'description', 'category_id')
            ->with([
                'category:id,name,color,icon'
            ])
            ->withCount([
                'documents as total' => function ($query) use ($userId) {
                    $query->withTrashed()
                        ->whereHas('transcript', function ($q) use ($userId) {
                            $q->withTrashed()
                                ->where('user_id', $userId);
                        });
                }
            ]);
    }

    public function listCountCategories()
    {
        $categories = DocumentTemplateCategory::select('name')
            ->withCount('templates as total')
            ->get();

        $total = DocumentTemplate::count();

        return [
            'total' => $total,
            'categories' => $categories
        ];
    }

    public function listIdNameTemplate()
    {
        return DocumentTemplate::select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}
