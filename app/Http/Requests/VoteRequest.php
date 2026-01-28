<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Position;
use App\Models\Candidate;

class VoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized by policy/middleware later
    }

    public function rules(): array
    {
        return [
            'votes' => ['required', 'array'],
            'votes.*' => ['required', 'integer', 'exists:candidates,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $election = $this->route('election');
            if (!$election) {
                return;
            }

            $votes = $this->input('votes', []);
            $positionIds = array_keys($votes);
            $candidateIds = array_values($votes);

            // 1. Check if all positions belong to this election
            $validPositionsCount = Position::whereIn('id', $positionIds)
                ->where('election_id', $election->id)
                ->count();
            
            if ($validPositionsCount !== count($positionIds)) {
                $validator->errors()->add('votes', 'One or more positions do not belong to this election.');
            }

            // 2. Check if candidates belong to the specific positions
            // We need to verify that for each (position_id => candidate_id), the candidate actually belongs to that position
            // and is vetting_status passed (if applicable, though request validation might be too early for complex status checks, 
            // but we can check the relationship).
            
            // Optimization: Fetch all candidates in the vote and their position_ids
            $candidates = Candidate::whereIn('id', $candidateIds)->get()->keyBy('id');

            foreach ($votes as $positionId => $candidateId) {
                $candidate = $candidates->get($candidateId);
                
                if (!$candidate) {
                    $validator->errors()->add("votes.$positionId", "Invalid candidate selected.");
                    continue;
                }

                if ($candidate->position_id != $positionId) {
                    $validator->errors()->add("votes.$positionId", "Candidate does not run for this position.");
                }

                // CRITICAL: Uncomment this!
                if ($candidate->vetting_status !== 'passed' || $candidate->nomination_status !== 'approved') {
                $validator->errors()->add("votes.$positionId", "You cannot vote for a candidate who is not approved.");
                }
            }
        });
    }
}
