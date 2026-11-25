/**
 * The insurance editor's prompt to reopen charges: after a policy is saved, lists the visits that were
 * billed before the policy was on file and lets a biller send the chosen ones back to the Billing Manager.
 * The markup is the #reopen-charges-panel block in insurance_edit.html.twig, which is only rendered for
 * users who may bill.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
export class ReopenChargesPanel
{
    #service;
    #panel;

    /**
     * @param {InsurancePolicyService} insurancePolicyService
     * @param {?HTMLElement} panel the #reopen-charges-panel element, or null when the user may not bill
     */
    constructor(insurancePolicyService, panel) {
        this.#service = insurancePolicyService;
        this.#panel = panel;
        if (!panel) {
            return;
        }
        panel.querySelector('.btn-reopen-charges').addEventListener('click', () => this.#reopenSelected());
        panel.querySelectorAll('.btn-reopen-charges-dismiss').forEach(button => {
            button.addEventListener('click', () => this.hide());
        });
        panel.querySelector('.reopen-charges-all').addEventListener('change', evt => {
            this.#checkboxes().forEach(checkbox => {
                checkbox.checked = evt.target.checked;
            });
            this.#updateReopenButton();
        });
    }

    /**
     * Asks the server which visits were never billed to the saved policies' payers and lists them. A visit
     * two policies both claim is listed once, for the first of them. With nothing to list the panel is hidden.
     *
     * @param {InsurancePolicyModel[]} policies the policies just saved, the one billed first (primary) first
     * @returns {Promise<void>} settles once the panel is up to date; never rejects, as the save itself succeeded
     */
    offer(policies) {
        if (!this.#panel) {
            return Promise.resolve();
        }
        this.hide();
        return Promise.all(policies.map(policy => this.#service.getReopenCandidates(policy)))
            .then(candidateLists => {
                let rows = [];
                let listed = new Set();
                candidateLists.forEach((candidates, index) => {
                    candidates.forEach(candidate => {
                        if (!listed.has(candidate.encounter)) {
                            listed.add(candidate.encounter);
                            rows.push({policy: policies[index], candidate: candidate});
                        }
                    });
                });
                if (rows.length > 0) {
                    this.#show(rows);
                }
            })
            .catch(error => {
                console.error(error);
            });
    }

    hide() {
        if (this.#panel) {
            this.#panel.classList.add('d-none');
        }
    }

    #show(rows) {
        let body = this.#panel.querySelector('tbody');
        body.replaceChildren(...rows.map(row => this.#buildRow(row.policy, row.candidate)));
        this.#panel.querySelector('.reopen-charges-all').checked = true;
        this.#panel.querySelector('.reopen-charges-offer').classList.remove('d-none');
        this.#panel.querySelector('.reopen-charges-done').classList.add('d-none');
        this.#panel.querySelector('.reopen-charges-failed').classList.add('d-none');
        this.#updateReopenButton();
        this.#panel.classList.remove('d-none');
        if (typeof this.#panel.scrollIntoView === 'function') {
            this.#panel.scrollIntoView({block: 'nearest'});
        }
    }

    #buildRow(policy, candidate) {
        let row = document.createElement('tr');
        let checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = true;
        checkbox.className = 'reopen-charges-encounter';
        checkbox.value = candidate.encounter;
        checkbox.dataset.policyUuid = policy.uuid;
        checkbox.setAttribute('aria-label', this.#panel.dataset.selectLabel + ' ' + candidate.encounter);
        checkbox.addEventListener('change', () => this.#updateReopenButton());
        let cells = [
            checkbox,
            window.top.oeFormatters.I18NDateFormat(new Date(candidate.date + 'T00:00:00')),
            String(candidate.encounter),
            candidate.last_billed_to || this.#panel.dataset.noPayer,
            candidate.balance
        ];
        cells.forEach((content, index) => {
            let cell = document.createElement('td');
            if (index === cells.length - 1) {
                cell.className = 'text-right';
            }
            cell.append(content);
            row.append(cell);
        });
        return row;
    }

    #checkboxes() {
        return Array.from(this.#panel.querySelectorAll('.reopen-charges-encounter'));
    }

    #updateReopenButton() {
        this.#panel.querySelector('.btn-reopen-charges').disabled = !this.#checkboxes().some(checkbox => checkbox.checked);
    }

    #reopenSelected() {
        let encountersByPolicy = new Map();
        this.#checkboxes().filter(checkbox => checkbox.checked).forEach(checkbox => {
            let encounters = encountersByPolicy.get(checkbox.dataset.policyUuid) || [];
            encounters.push(Number(checkbox.value));
            encountersByPolicy.set(checkbox.dataset.policyUuid, encounters);
        });
        let button = this.#panel.querySelector('.btn-reopen-charges');
        button.disabled = true;
        let requests = Array.from(encountersByPolicy, ([policyUuid, encounters]) => {
            return this.#service.reopenCharges(policyUuid, encounters);
        });
        return Promise.all(requests)
            .then(() => {
                this.#panel.querySelector('.reopen-charges-offer').classList.add('d-none');
                this.#panel.querySelector('.reopen-charges-done').classList.remove('d-none');
            })
            .catch(error => {
                console.error(error);
                this.#panel.querySelector('.reopen-charges-failed').classList.remove('d-none');
                button.disabled = false;
            });
    }
}
