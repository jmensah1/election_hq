<?php

use App\Models\User;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use App\Models\Vote;
use App\Services\ResultsService;

// Ensure we have an organization (assuming first one exists)
$org = \App\Models\Organization::first();
if (!$org) {
    echo "No organization found.\n";
    exit;
}

echo "Base Organization: {$org->name}\n";

$creator = User::first();
if (!$creator) {
    $creator = User::factory()->create();
}

// Create a test election
$election = Election::create([
    'organization_id' => $org->id,
    'created_by' => $creator->id,
    'title' => 'Test Tie Election ' . uniqid(),
    'slug' => 'test-tie-' . uniqid(),
    'status' => 'voting',
    'nomination_start_date' => now()->subDays(2),
    'nomination_end_date' => now()->subDays(1),
    'vetting_start_date' => now()->subDays(1),
    'vetting_end_date' => now()->subDays(1),
    'voting_start_date' => now()->subHour(),
    'voting_end_date' => now()->addHour(),
]);

echo "Created Election: {$election->id}\n";

// Create a position
$position = Position::create([
    'organization_id' => $org->id,
    'election_id' => $election->id,
    'name' => 'President',
    'max_candidates' => 1, // Logic should ignore this for ties
]);

echo "Created Position: {$position->id}\n";

// Create 2 Users and Candidates
$user1 = User::factory()->create();
$user2 = User::factory()->create();

$c1 = Candidate::create([
    'organization_id' => $org->id,
    'user_id' => $user1->id,
    'election_id' => $election->id,
    'position_id' => $position->id,
    'nomination_status' => 'approved',
    'vetting_status' => 'passed',
    'email' => $user1->email,
]);

$c2 = Candidate::create([
    'organization_id' => $org->id,
    'user_id' => $user2->id,
    'election_id' => $election->id,
    'position_id' => $position->id,
    'nomination_status' => 'approved',
    'vetting_status' => 'passed',
    'email' => $user2->email,
]);

echo "Created Candidates: {$c1->id}, {$c2->id}\n";

// Cast 1 vote for each (Tie)
Vote::create(['organization_id' => $org->id, 'election_id' => $election->id, 'position_id' => $position->id, 'candidate_id' => $c1->id, 'user_id' => $user1->id, 'encrypted_vote' => 'dummy']);
Vote::create(['organization_id' => $org->id, 'election_id' => $election->id, 'position_id' => $position->id, 'candidate_id' => $c2->id, 'user_id' => $user2->id, 'encrypted_vote' => 'dummy']);

echo "Votes Cast.\n";

// Run logic
app(ResultsService::class)->determineWinners($election);

// Refresh
$c1->refresh();
$c2->refresh();

echo "Candidate 1 Winner: " . ($c1->is_winner ? 'YES' : 'NO') . "\n";
echo "Candidate 2 Winner: " . ($c2->is_winner ? 'YES' : 'NO') . "\n";

if ($c1->is_winner && $c2->is_winner) {
    echo "SUCCESS: Both are winners!\n";
} else {
    echo "FAILURE: Tie logic incorrect.\n";
}

// Cleanup
Vote::where('election_id', $election->id)->delete();
$c1->delete();
$c2->delete();
$position->delete();
$election->delete();
