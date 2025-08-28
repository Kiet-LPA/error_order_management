@if($followers->count() > 0)
    @foreach($followers as $follower)
        <div class="d-flex justify-content-between align-items-center mb-2 p-2" style="background: #f8f9fa; border-radius: 8px;">
            <div>
                <span class="fw-medium">{{ $follower->name }} - {{ ucfirst($follower->role) }}</span>
                @if($follower->department)
                    <small class="text-muted d-block">{{ $follower->department->name }}</small>
                @endif
            </div>
            <button class="btn btn-sm btn-outline-danger" onclick="removeFollower({{ $follower->id }})">
                <i class="bi bi-x"></i>
            </button>
        </div>
    @endforeach
@else
    <div class="text-muted text-center py-3">
        <i class="bi bi-people text-muted"></i>
        <div>Chưa có người theo dõi</div>
    </div>
@endif
