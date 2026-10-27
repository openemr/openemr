# Cross-cutting subsystems

Part of the [OpenEMR architecture docs](README.md). Beyond the request-pipeline layers
([layers.md](layers.md)), these subsystems are touched by nearly every feature —
authorization, file storage, document management, internationalization, and encryption. Each
has an established mechanism to use and a migration direction to follow. The interface shell
and forms are large enough to have their own document:
[interface-and-forms.md](interface-and-forms.md).

---

## Permissions and authorization

OpenEMR's access control is an **ACL system built on phpGACL**, surfaced through
`src/Common/Acl/AclMain.php`. The model has three pieces:

- **ACO (Access Control Object)** — the thing being protected, identified by a *section* +
  *value*. Sections group related rights (`admin`, `acct`, `patients`, `encounters`,
  `sensitivities`, …); values name the specific right within a section (`super`, `bill`,
  `demo`, `med`, often with `write`/`addonly` return levels). The full catalog is documented
  at the top of `AclMain.php`.
- **ACL** — binds user groups to the ACOs they may access.
- **Principal** — the user and group membership the check runs against.

The runtime API is static: `AclMain::aclCheckCore($section, $value, $user, $return_value)`
returns the user's right, with helpers `aclCheckForm()`, `aclCheckIssue()`,
`aclCheckAcoSpec()`. `AclExtended` and the `Gacl*` classes back administration. Access-denied
handling has a modern path (`AccessDeniedException`, `AccessDeniedHelper`,
`AccessDeniedResponseFormat`).

Rules and direction:

- **Check at the boundary, early.** A controller action verifies permission before doing work
  and returns an access-denied `Response` on failure — see `CarePlanController`'s
  `hasFormPermission` guard ([controller layer](layers.md#controller-layer)).
- **Route checks through `AclMain`,** not bespoke role-string comparisons scattered through
  the code.
- **Target: typed principals and scoped access.** Per `AGENTS.md` → Authorization Modeling,
  new code passes a typed principal (e.g. `ClinicalUser`) rather than a bare user-id string,
  and encodes facility/tenant scope in the type (a scoped repository) instead of re-checking
  everywhere. The static `AclMain::aclCheckCore` global call is the legacy surface; wrap it
  behind an injected authorization service as this migrates.
- **API authorization** is layered on top via OAuth2 scopes (`league/oauth2-server`) — see
  [api.md](api.md).

---

## Filesystem abstraction (Flysystem)

File access goes through **League Flysystem** (`league/flysystem`), not raw `fopen`,
`file_get_contents`, or hardcoded `sites/<site>/…` paths. The project wraps it in
`src/Services/Storage/`:

- **`ManagerInterface::getStorage(Location): FilesystemOperator`** is the single accessor.
  Consumers depend on `ManagerInterface` and on Flysystem's `FilesystemOperator` — never on
  the concrete `Manager` or on a filesystem path string.
- **`Manager`** is a registry: filesystems are registered by `Location` during bootstrap (via
  DI/factory), and consumers retrieve them by `Location` rather than constructing them.
- Adapters are swappable — `flysystem-local` on disk, `flysystem-memory` for tests, cloud/
  object-store adapters later — with no change to consumer code. This is what makes a
  file-touching service unit-testable (memory adapter) and deployment-flexible (local vs
  cloud).

Rules and direction:

- **New file I/O uses a `FilesystemOperator` obtained from `ManagerInterface`,** injected —
  not raw filesystem calls from business logic.
- Legacy code that computes site paths and calls PHP filesystem functions directly is the
  thing being migrated; route it through a `Location` + Flysystem as you touch it.
- Document storage (below) is the largest consumer of this abstraction.

---

## Document management

Patient documents (scanned files, uploads, generated PDFs/CDAs) are modeled today by the
legacy **`library/classes/Document.class.php`** against the `documents` table, with a modern
service surface emerging in **`src/Services/DocumentService.php`**.

Current mechanics:

- **Two storage backends, chosen per document:** `STORAGE_METHOD_FILESYSTEM` (on disk under
  `sites/<site>/documents/…`) and `STORAGE_METHOD_COUCHDB` (offsite in CouchDB via
  `library/classes/CouchDB.class.php`, tracked by `couch_docid`/`couch_revid`). The storage
  method is a column on the document row.
- **Optional encryption at rest** (`ENCRYPTED_ON`/`OFF`) via `CryptoGen` (see
  [Security](#security-encryption-and-key-management) below).
- **Categorization** through a document category tree; **thumbnails** via
  `ThumbnailGenerator`.
- **External exposure** through FHIR `DocumentReference`
  (`src/Services/FHIR/FhirDocumentReferenceService.php`) and the document download services.

Direction:

- `Document.class.php` is a legacy god-object with a hardcoded storage-method `switch`. The
  target is `DocumentService` over the Flysystem Storage abstraction (above), where
  filesystem / CouchDB / cloud become **adapters behind one `FilesystemOperator`** instead of
  a branch on a storage-method integer. Encryption, categorization, and FHIR mapping stay in
  services.
- Build new document features against `DocumentService`, not by extending
  `Document.class.php`.

---

## Internationalization (i18n)

All user-facing text is translated through the **`xl()` family** in
`library/translation.inc.php`, backed by the `lang_constants` table, a per-language
`TranslationCache`, and the session's `language_choice`.

The functions encode *output context* — translation and escaping are one decision, so pick
the variant matching where the string lands:

| Function | Does | Use for |
|----------|------|---------|
| `xl()`   | translate only | a string you will escape yourself / a non-HTML context |
| `xlt()`  | translate + HTML-escape | text in HTML |
| `xla()`  | translate + attribute-escape | HTML attribute values |
| `xlj()`  | translate + JS-escape | strings emitted into JavaScript |

Rules:

- **Wrap every user-facing literal** in the context-correct `xl*` function. A bare string in
  the UI is an i18n bug.
- **Pass only source-code literals to `xl()`.** The parameter is typed `literal-string`
  precisely because strings are extracted and matched by exact value; a dynamic value (DB
  column, request input, concatenation) cannot be translated and signals the value should
  have been narrowed to a known string at the call site. Build sentences from whole
  translatable units, not concatenated fragments.
- **In Twig,** autoescaping already handles HTML escaping — translate the text and let the
  template escape, rather than double-escaping with `xlt()` inside an already-escaped context.
- Modern helpers: `src/Services/Utils/TranslationService.php`,
  `src/Common/Translation/TranslationCache.php`; warm the cache early (`xlWarmCache()`) in
  long request flows.

---

## Security: encryption and key management

At-rest encryption is centralized in **`src/Common/Crypto/CryptoGen.php`**
(`CryptoInterface`), with a deliberately layered key architecture (documented in full at the
top of the class):

- **Two independent key sets.** A **database key set** in the `keys` MySQL table, and a
  **drive key set** on disk under `sites/<site>/documents/logs_and_misc/methods/`. The drive
  key set is itself **encrypted by the database key set.**
- **Cross-store protection.** Data stored on the *drive* is encrypted with the *database*
  keys, and data stored in the *database* is encrypted with the *drive* keys — so
  compromising one store alone does not yield the key needed to read its contents.
- **Versioned algorithms.** `KeySource`/`KeyVersion` and a versioning scheme let the
  algorithm improve over time while older ciphertext stays decryptable (backwards-compatible
  `decryptStandard(..., minimumVersion)`). `KeyV7Generator` and `LegacyKeychainLoader` manage
  generation and migration of key material.
- **Passphrase mode** (`PasswordBasedCrypto`) for encrypt/decrypt without the private key
  sets.
- **API surface:** `encryptStandard($value, KeySource)`, `decryptStandard(...)`,
  `cryptCheckStandard()`. Keys are cached per request inside the object for performance.

Rules and direction:

- **Use `CryptoInterface`/`CryptoGen` for at-rest encryption** — never hand-roll
  openssl/sodium. Inject `CryptoInterface`, do not `new CryptoGen()` in new code.
- **Protect the drive key material operationally.** The `methods/` drive keys are part of the
  trust root: if that directory is wiped or regenerated (for example an ephemeral documents
  volume in a container deployment), previously-encrypted data and HMAC-derived secrets
  become undecryptable *even though the `keys` table is intact*. Persist
  `sites/<site>/documents/` across deployments.
- **Related security components:** CSRF tokens (`CsrfUtils`, enforced on writes — see
  `CarePlanController::saveAction`), authentication (`src/Common/Auth/AuthUtils.php`), and API
  authorization via OAuth2 (`league/oauth2-server`, [api.md](api.md)). Per `AGENTS.md` →
  Error Handling, never leak exception messages (which may carry SQL, paths, or secrets) to
  users.
