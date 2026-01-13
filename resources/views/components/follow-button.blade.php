@auth
    @if (auth()->id() !== $user->id)
        <div x-data="{
            isFollowing: {{ $isFollowing ? 'true' : 'false' }},
            followersCount: {{ $followersCount }},
            loading: false,
            hover: false,
        
            toggleFollow() {
                if (this.loading) return;
                this.loading = true;
        
                axios.post('{{ route('users.follow.toggle', $user) }}')
                    .then(res => {
                        this.isFollowing = res.data.isFollowing;
                        this.followersCount = res.data.followersCount;
                    })
                    .finally(() => {
                        this.loading = false;
                        this.hover = false;
                    });
            }
        }" class="d-inline-flex align-items-center gap-2">

            <button @click="toggleFollow" @mouseenter="hover = true" @mouseleave="hover = false" :disabled="loading"
                class="btn btn-sm rounded-pill"
                :class="isFollowing
                    ?
                    (hover ? 'btn-danger' : 'btn-outline-secondary') :
                    'btn-primary'"
                x-text="isFollowing
            ? (hover ? 'Unfollow' : 'Following')
            : 'Follow'"></button>
        </div>
    @endif
@endauth
