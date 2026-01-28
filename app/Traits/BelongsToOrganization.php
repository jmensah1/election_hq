<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToOrganization
{
    /**
     * Boot the BelongsToOrganization trait.
     */
    protected static function bootBelongsToOrganization()
    {
        // Automatically set organization_id on create
        static::creating(function ($model) {
            if (empty($model->organization_id) && auth()->check() && auth()->user()->organization_id) {
                // Assuming we will have a helper or middleware to set the current org on the user instance or globally
                // For now, let's assume we can get it from the authenticated user or a global helper
                // In Phase 3, we will implement current_organization_id() helper
                if (function_exists('current_organization_id') && current_organization_id()) {
                     $model->organization_id = current_organization_id();
                }
            }
        });

        // Add global scope to filter by organization
        static::addGlobalScope('organization', function (Builder $builder) {
            // We only apply this scope if we are in a tenant context
            // and NOT if we are in a super-admin context (unless explicitly requested)
            
            if (function_exists('current_organization_id') && current_organization_id()) {
                $builder->where('organization_id', current_organization_id());
            }
        });
    }

    /**
     * Get the organization that owns the model.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
