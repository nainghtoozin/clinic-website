<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    protected string $permissionPrefix = '';

    public function __construct()
    {
        if ($this->permissionPrefix) {
            $this->middleware("permission:{$this->permissionPrefix}.view")
                ->only(['index', 'show']);

            $this->middleware("permission:{$this->permissionPrefix}.create")
                ->only(['create', 'store']);

            $this->middleware("permission:{$this->permissionPrefix}.edit")
                ->only(['edit', 'update']);

            $this->middleware("permission:{$this->permissionPrefix}.delete")
                ->only(['destroy']);
        }
    }
}
