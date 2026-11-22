# Shared cases for the swarm leader election functions in the release, flex
# and binary openemr.sh scripts. Load after '../helpers' and call
# load_swarm_leader_functions "<script>" in setup().

# Defines the leader election functions from an openemr.sh in the current
# shell, without running the rest of the entrypoint.
load_swarm_leader_functions() {
    local script="$1"
    local defs
    defs=$(awk '
        /^current_leader_file\(\) \{/ { on = 1 }
        on { print }
        on && /^update_leader_heartbeat\(\) \{/ { last = 1 }
        last && /^\}/ { exit }
    ' "$script")
    [[ "$defs" == *"update_leader_heartbeat()"* ]] || {
        echo "leader election functions not found in $script"
        return 1
    }
    eval "$defs"
    export OE_ROOT="${BATS_TEST_TMPDIR}/oe"
    mkdir -p "${OE_ROOT}/sites"
    LEASE="${OE_ROOT}/sites/docker-leader"
}

# Sets a file's mtime to N seconds ago (GNU or BSD touch).
age_file() {
    local file="$1" seconds="$2" stamp
    stamp=$(( $(date +%s) - seconds ))
    touch -d "@${stamp}" "$file" 2>/dev/null || touch -t "$(date -r "$stamp" +%Y%m%d%H%M.%S)" "$file"
}

case_first_claim_takes_the_base_lease() {
    try_become_leader
    [[ "$AUTHORITY" = "yes" ]]
    [[ "$LEADER_FILE" = "$LEASE" ]]
    [[ -f "$LEASE" ]]
}

case_fresh_lease_makes_a_follower() {
    date +%s > "$LEASE"
    try_become_leader
    [[ "$AUTHORITY" = "no" ]]
    [[ ! -e "${LEASE}.1" ]]
}

case_completed_setup_makes_a_follower() {
    touch "${OE_ROOT}/sites/docker-completed"
    try_become_leader
    [[ "$AUTHORITY" = "no" ]]
    [[ ! -e "$LEASE" ]]
}

case_stale_lease_is_taken_over_without_deleting_it() {
    echo old > "$LEASE"
    age_file "$LEASE" 301
    try_become_leader
    [[ "$AUTHORITY" = "yes" ]]
    [[ "$LEADER_FILE" = "${LEASE}.1" ]]
    [[ "$(cat "$LEASE")" = "old" ]]
    [[ "$(current_leader_file)" = "${LEASE}.1" ]]
}

case_stale_takeover_lease_is_taken_over_by_next_generation() {
    date +%s > "$LEASE"
    date +%s > "${LEASE}.1"
    age_file "$LEASE" 900
    age_file "${LEASE}.1" 301
    try_become_leader
    [[ "$AUTHORITY" = "yes" ]]
    [[ "$LEADER_FILE" = "${LEASE}.2" ]]
}

case_heartbeat_refreshes_only_own_lease() {
    echo old > "$LEASE"
    age_file "$LEASE" 301
    try_become_leader
    age_file "${LEASE}.1" 301
    is_lease_stale "${LEASE}.1"
    update_leader_heartbeat
    is_leader_stale && return 1
    # The stale base lease must stay untouched (and stale).
    [[ "$(cat "$LEASE")" = "old" ]]
    is_lease_stale "$LEASE"
}

case_no_lease_counts_as_stale() {
    is_leader_stale
}

# Many followers notice the same stale leader at different times, spread over
# about 4 s, while file deletes on the shared volume stall for 1.5 s (as they
# can on NFS). Exactly one may take over. The old delete-then-create takeover
# elected several leaders here: a follower that judged the leader stale
# before another follower claimed the lease deleted that new lease when its
# stalled delete finally ran, then claimed its own.
case_concurrent_takeovers_elect_one_leader() {
    local results="${BATS_TEST_TMPDIR}/results"
    mkdir -p "$results"
    echo old > "$LEASE"
    age_file "$LEASE" 301
    rm() { sleep 1.5; command rm "$@"; }
    local -i i
    for i in $(seq 1 20); do
        (
            sleep "$(( i / 5 )).$(( (i * 37) % 100 ))"
            try_become_leader >/dev/null
            echo "$AUTHORITY" > "${results}/${i}"
        ) &
    done
    wait
    unset -f rm
    local leaders
    leaders=$(grep -l '^yes$' "$results"/* | wc -l | tr -d ' ')
    [[ "$leaders" = "1" ]] || { echo "expected 1 leader, got $leaders"; return 1; }
}
