<?php

use App\Models\Organization;

if (! function_exists('current_organization')) {
    /**
     * Get the current organization instance.
     *
     * @return \App\Models\Organization|null
     */
    function current_organization(): ?Organization
    {
        return app()->has('current_organization') 
            ? app('current_organization') 
            : null;
    }
}

if (! function_exists('current_organization_id')) {
    /**
     * Get the current organization ID.
     *
     * @return int|null
     */
    function current_organization_id(): ?int
    {
        return current_organization()?->id;
    }
}
