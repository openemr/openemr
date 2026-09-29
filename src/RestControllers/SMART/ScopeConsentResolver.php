<?php

/**
 * Turns the user's choices on the SMART consent form into the list of scopes to grant.
 *
 * The previous design rebuilt scope strings in browser JavaScript from a merged per-resource
 * card (one "version" per card, actions OR'ed together) and then required each rebuilt string
 * to be contained by a single requested scope. Any request that split a resource's
 * permissions across scopes -- `.rs` + `.cud` (exactly what the server advertises), `.read` +
 * `.cu`, `.rs` + `.write` -- rebuilt to a union that no single requested scope contained, and
 * the grant was silently dropped.
 *
 * This resolver never invents a scope. It walks the scopes the client requested and, for
 * each one, intersects it with what the user left checked:
 *
 *  - fully approved   -> the requested string verbatim (v1 stays v1, v2 stays v2)
 *  - partly approved  -> the v2 CRUDS subset, keeping the requested constraint
 *  - not approved     -> dropped
 *
 * so the granted set is always a subset of the request and always passes the grant check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\RestControllers\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Validators\ScopeValidatorFactory;

final class ScopeConsentResolver
{
    private const ACTION_ORDER = ['c', 'r', 'u', 'd', 's'];

    /**
     * @param list<string> $requestedScopes Scopes the client asked for (post client-registration filter)
     * @param array<array-key, mixed> $structuredScopes ScopePermissionParser::parseScopes() output for the same request
     * @param array<array-key, mixed> $postedPlainScopes The `scope[...]` form values (openid, launch, operations, ...)
     * @param array<array-key, mixed> $postedGrants The `grant[<cardKey>][actions|categories][]` form values
     * @return list<string>
     */
    public function resolve(array $requestedScopes, array $structuredScopes, array $postedPlainScopes, array $postedGrants): array
    {
        $plainApproved = self::stringValues($postedPlainScopes);
        $cardCategories = self::cardCategories($structuredScopes);

        $granted = [];
        foreach ($requestedScopes as $requested) {
            try {
                $entity = ScopeEntity::createFromString($requested);
            } catch (\InvalidArgumentException) {
                continue;
            }

            $cardKey = ($entity->getContext() ?? '') . '-' . ($entity->getResource() ?? '');
            $isCardScope = $entity->isResourcePermissionScope()
                && array_key_exists($cardKey, $cardCategories);

            // A requested scope posted back verbatim in scope[...] is approved as-is. Non-card
            // scopes always arrive this way; for card scopes it keeps direct POST callers and
            // customized consent templates working. It can never widen the grant because the
            // value must equal a scope the client requested.
            if (in_array($requested, $plainApproved, true)) {
                $granted[] = $requested;
                continue;
            }
            if (!$isCardScope) {
                continue;
            }

            $grant = $postedGrants[$cardKey] ?? null;
            if (!is_array($grant)) {
                continue; // card absent from the post: nothing on it was approved
            }
            $approvedActions = array_values(array_intersect(
                self::requestedActions($entity),
                self::stringValues(is_array($grant['actions'] ?? null) ? $grant['actions'] : [])
            ));
            if ($approvedActions === []) {
                continue;
            }
            // only categories the form actually offered for this card are honored
            $offeredCategories = $cardCategories[$cardKey];
            $approvedCategories = array_values(array_intersect(
                $offeredCategories,
                self::stringValues(is_array($grant['categories'] ?? null) ? $grant['categories'] : [])
            ));

            foreach ($this->emit($requested, $entity, $approvedActions, $offeredCategories, $approvedCategories) as $scope) {
                $granted[] = $scope;
            }
        }

        return array_values(array_unique($granted));
    }

    /**
     * Keep only the resource permission scopes the client is registered for. Fails closed: with
     * no registration, no resource permission scope survives. Non-resource scopes (openid,
     * launch/patient, api:*, offline_access, operations) and unparsable strings are passed
     * through to the existing grant checks, which reject them on their own terms.
     *
     * @param list<string> $scopes
     * @param list<string> $registeredScopes
     * @return list<string>
     */
    public function filterToClientRegistration(array $scopes, array $registeredScopes): array
    {
        $validators = (new ScopeValidatorFactory())->buildScopeValidatorArray($registeredScopes);
        $filtered = [];
        foreach ($scopes as $scope) {
            try {
                $entity = ScopeEntity::createFromString($scope);
            } catch (\InvalidArgumentException) {
                $filtered[] = $scope;
                continue;
            }
            if (!$entity->isResourcePermissionScope()) {
                $filtered[] = $scope;
                continue;
            }
            $key = $entity->getScopeLookupKey();
            if (isset($validators[$key]) && $validators[$key]->grantsScope($entity)) {
                $filtered[] = $scope;
            }
        }
        return $filtered;
    }

    /**
     * @param list<string> $approvedActions
     * @param list<string> $offeredCategories
     * @param list<string> $approvedCategories
     * @return list<string>
     */
    private function emit(string $requested, ScopeEntity $entity, array $approvedActions, array $offeredCategories, array $approvedCategories): array
    {
        $fullyApproved = count($approvedActions) === count(self::requestedActions($entity));
        $base = ($entity->getContext() ?? '') . '/' . ($entity->getResource() ?? '') . '.' . self::orderedActions($approvedActions);
        $query = self::queryOf($requested);

        if ($query !== null) {
            // A constrained request. If its constraint is a category the form offered, the
            // category checkbox decides; any other constraint cannot be toggled and passes.
            $category = self::categoryOf($query);
            if ($category !== null && in_array($category, $offeredCategories, true) && !in_array($category, $approvedCategories, true)) {
                return [];
            }
            return [$fullyApproved ? $requested : $base . '?' . $query];
        }

        // Unconstrained request. All offered categories left checked (or none offered) keeps it
        // unrestricted; unchecking any narrows it to the categories that remain.
        if ($offeredCategories === [] || count($approvedCategories) === count($offeredCategories)) {
            return [$fullyApproved ? $requested : $base];
        }
        return array_map(static fn(string $category): string => $base . '?category=' . $category, $approvedCategories);
    }

    /**
     * @return list<string>
     */
    private static function requestedActions(ScopeEntity $entity): array
    {
        $permissions = $entity->getPermissions();
        $actions = [];
        if ($permissions->create) {
            $actions[] = 'c';
        }
        if ($permissions->read) {
            $actions[] = 'r';
        }
        if ($permissions->update) {
            $actions[] = 'u';
        }
        if ($permissions->delete) {
            $actions[] = 'd';
        }
        if ($permissions->search) {
            $actions[] = 's';
        }
        return $actions;
    }

    /**
     * @param list<string> $actions
     */
    private static function orderedActions(array $actions): string
    {
        return implode('', array_values(array_filter(self::ACTION_ORDER, static fn(string $a): bool => in_array($a, $actions, true))));
    }

    private static function queryOf(string $scope): ?string
    {
        $pos = strpos($scope, '?');
        if ($pos === false) {
            return null;
        }
        $query = substr($scope, $pos + 1);
        return $query === '' ? null : $query;
    }

    /**
     * Mirrors ScopePermissionParser::parseScopeString() so the values match the checkbox values.
     */
    private static function categoryOf(string $query): ?string
    {
        return preg_match('/category=(.+)/', $query, $matches) === 1 ? $matches[1] : null;
    }

    /**
     * @param array<array-key, mixed> $structuredScopes
     * @return array<string, list<string>> card key => category values offered on that card
     */
    private static function cardCategories(array $structuredScopes): array
    {
        $cards = [];
        foreach ($structuredScopes as $key => $card) {
            if (!is_string($key) || !is_array($card)) {
                continue;
            }
            $categories = [];
            $restrictions = $card['restrictions'] ?? [];
            if (is_array($restrictions)) {
                foreach ($restrictions as $restriction) {
                    if (is_array($restriction) && is_string($restriction['value'] ?? null)) {
                        $categories[] = $restriction['value'];
                    }
                }
            }
            $cards[$key] = $categories;
        }
        return $cards;
    }

    /**
     * @param array<array-key, mixed> $values
     * @return list<string>
     */
    private static function stringValues(array $values): array
    {
        return array_values(array_filter($values, is_string(...)));
    }
}
