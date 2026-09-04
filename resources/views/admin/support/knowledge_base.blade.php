@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 text-gray-800">Knowledge Base Articles</h1>
        <p class="text-muted small">Manage internal and customer-visible troubleshooting articles and guides</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Visibility</th>
                            <th>Published</th>
                            <th>Author</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $art)
                            <tr>
                                <td class="fw-bold">{{ $art->title }}</td>
                                <td>{{ $art->category->name ?? 'Uncategorized' }}</td>
                                <td><span class="badge bg-info text-dark">{{ $art->visibility }}</span></td>
                                <td>
                                    @if($art->is_published)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>{{ $art->author->name ?? 'System' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No knowledge base articles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2 border-top">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
