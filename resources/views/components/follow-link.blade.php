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

            <a href="#" @click.prevent="toggleFollow" @mouseenter="hover = true" @mouseleave="hover = false"
                class="small fw-semibold text-decoration-none"
                :class="isFollowing
                    ?
                    (hover ? 'text-danger' : 'text-muted') :
                    'text-primary'"
                x-text="isFollowing
            ? (hover ? 'Unfollow' : 'Following')
            : 'Follow'"></a>
        </div>
    @endif
@endauth
