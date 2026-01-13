<x-app-layout>
    <div class="row g-3">

        <!-- LEFT SIDEBAR -->
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="card p-3 mb-3">
                <h6 class="fw-bold">Navigation</h6>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="#">Feed</a>
                    <a class="nav-link" href="#">Friends</a>
                    <a class="nav-link" href="#">Groups</a>
                    <a class="nav-link" href="#">Events</a>
                </nav>
            </div>

            <div class="card p-3">
                <h6 class="fw-bold">Shortcuts</h6>
                <ul class="list-unstyled small">
                    <li>Saved Posts</li>
                    <li>Memories</li>
                    <li>Marketplace</li>
                </ul>
            </div>
        </aside>

        <!-- CENTER FEED -->
        <section class="col-lg-6">

            <!-- STORIES -->
            <div class="d-flex gap-3 mb-3 overflow-auto">
                <div class="text-center">
                    <img src="https://picsum.photos/id/1005/60" class="story-avatar">
                    <small class="d-block">You</small>
                </div>
                <div class="text-center">
                    <img src="https://picsum.photos/id/1011/60" class="story-avatar">
                    <small class="d-block">Emma</small>
                </div>
                <div class="text-center">
                    <img src="https://picsum.photos/id/1012/60" class="story-avatar">
                    <small class="d-block">Ryan</small>
                </div>
            </div>

            <!-- CREATE POST -->
            <div class="card p-3 mb-3">
                <div class="d-flex align-items-center mb-2">
                    <img src="https://picsum.photos/40" class="rounded-circle me-2">
                    <input class="form-control" type="text" placeholder="What's on your mind?">
                </div>
                <div class="d-flex justify-content-around">
                    <button class="btn btn-light btn-sm">Photo</button>
                    <button class="btn btn-light btn-sm">Video</button>
                    <button class="btn btn-light btn-sm">Feeling</button>
                </div>
            </div>

            <!-- POST #1 -->
            <article class="card post-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <img src="https://picsum.photos/id/1015/40" class="rounded-circle me-2">
                        <div>
                            <h6 class="mb-0">Jane Doe</h6>
                            <small class="text-muted">2 hrs ago</small>
                        </div>
                    </div>
                    <p>Had a great hike today! 🌲🌄</p>
                    <img src="https://picsum.photos/id/1016/800/400" class="img-fluid rounded mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button class="reaction-btn me-3">
                                👍 Like
                            </button>
                            <button class="reaction-btn me-3">
                                💬 Comment
                            </button>
                            <button class="reaction-btn">
                                🔄 Share
                            </button>
                        </div>
                        <small class="text-muted">24 Likes • 6 Comments</small>
                    </div>
                </div>
            </article>

            <!-- POST #2 -->
            <article class="card post-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <img src="https://picsum.photos/id/1027/40" class="rounded-circle me-2">
                        <div>
                            <h6 class="mb-0">Alex Smith</h6>
                            <small class="text-muted">Yesterday</small>
                        </div>
                    </div>
                    <p>New setup, loving the vibe 💻✨</p>
                    <img src="https://picsum.photos/id/1033/800/400" class="img-fluid rounded mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button class="reaction-btn me-3">👍 Like</button>
                            <button class="reaction-btn me-3">💬 Comment</button>
                            <button class="reaction-btn">🔄 Share</button>
                        </div>
                        <small class="text-muted">41 Likes • 12 Comments</small>
                    </div>
                </div>
            </article>

            <!-- POST #3 -->
            <article class="card post-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <img src="https://picsum.photos/id/1001/40" class="rounded-circle me-2">
                        <div>
                            <h6 class="mb-0">Maria Lopez</h6>
                            <small class="text-muted">3 days ago</small>
                        </div>
                    </div>
                    <p>Sunday brunch with friends ☕🥐</p>
                    <img src="https://picsum.photos/id/1050/800/400" class="img-fluid rounded mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button class="reaction-btn me-3">👍 Like</button>
                            <button class="reaction-btn me-3">💬 Comment</button>
                            <button class="reaction-btn">🔄 Share</button>
                        </div>
                        <small class="text-muted">18 Likes • 3 Comments</small>
                    </div>
                </div>
            </article>

        </section>

        <!-- RIGHT SIDEBAR -->
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="card p-3 mb-3">
                <h6 class="fw-bold">Suggestions</h6>

                <div class="d-flex align-items-center mb-2">
                    <img src="https://picsum.photos/id/1021/40" class="rounded-circle me-2">
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">Chris</div>
                        <button class="btn btn-sm btn-outline-primary">
                            Add Friend
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <img src="https://picsum.photos/id/1022/40" class="rounded-circle me-2">
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">Sophia</div>
                        <button class="btn btn-sm btn-outline-primary">
                            Add Friend
                        </button>
                    </div>
                </div>
            </div>

            <div class="card p-3">
                <h6 class="fw-bold">Trending</h6>
                <ul class="list-unstyled small mb-0">
                    <li>#Travel</li>
                    <li>#Music</li>
                    <li>#Tech</li>
                </ul>
            </div>
        </aside>

    </div>

</x-app-layout>
