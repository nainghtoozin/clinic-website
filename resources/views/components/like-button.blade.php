@auth
    <span x-data="{
        liked: {{ $liked ? 'true' : 'false' }},
        likesCount: {{ $likesCount }},
        loading: false,
    
        toggleLike() {
            if (this.loading) return;
            this.loading = true;
    
            axios.post('{{ route('posts.like.toggle', $post) }}')
                .then(res => {
                    this.liked = res.data.liked;
                    this.likesCount = res.data.likesCount;
                })
                .finally(() => this.loading = false);
        }
    }" class="d-inline-flex align-items-center gap-1">
        <a href="#" @click.prevent="toggleLike" class="text-decoration-none"
            :class="liked ? 'text-danger' : 'text-muted'" style="user-select:none">
            <i class="bi" :class="liked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up'"></i>
        </a>

        <span class="small text-muted" x-text="likesCount"></span>
    </span>
@endauth
