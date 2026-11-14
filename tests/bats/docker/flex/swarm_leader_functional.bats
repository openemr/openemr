# BATS: flex openemr.sh — swarm leader election (lease claims and takeover)

load '../test_helper/bats-support/load'
load '../test_helper/bats-assert/load'
load '../helpers'
load '../swarm_leader'

setup() {
    load_swarm_leader_functions "$(get_script_dir flex)/openemr.sh"
}

@test "flex swarm leader: first claim takes the base lease" {
    case_first_claim_takes_the_base_lease
}

@test "flex swarm leader: a fresh lease makes a follower" {
    case_fresh_lease_makes_a_follower
}

@test "flex swarm leader: completed setup makes a follower" {
    case_completed_setup_makes_a_follower
}

@test "flex swarm leader: no lease counts as stale" {
    case_no_lease_counts_as_stale
}

@test "flex swarm leader: a stale lease is taken over without deleting it" {
    case_stale_lease_is_taken_over_without_deleting_it
}

@test "flex swarm leader: a stale takeover lease is taken over by the next generation" {
    case_stale_takeover_lease_is_taken_over_by_next_generation
}

@test "flex swarm leader: the heartbeat refreshes only this leader's lease" {
    case_heartbeat_refreshes_only_own_lease
}

@test "flex swarm leader: concurrent takeovers of a stale leader elect one leader" {
    case_concurrent_takeovers_elect_one_leader
}
