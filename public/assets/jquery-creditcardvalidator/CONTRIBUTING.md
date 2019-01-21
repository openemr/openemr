# Contributing

## How to

1. Search [existing issues](https://github.com/PawelDecowski/jquery-creditcardvalidator/issues) to make sure you’re not submitting a duplicate.
1. [Open a new issue](https://github.com/PawelDecowski/jquery-creditcardvalidator/issues/new). Let’s discuss it before you start writing code.
2. Grab the latest [stable](https://github.com/PawelDecowski/jquery-creditcardvalidator/tree/master) commit.
3. Create a development branch according to this naming scheme:
   
   `type/description`

   Where `type` is one of:
   * `feature`
   * `bug`
   * `chore`

   And `description` is an all-lowercase, hyphen-separated description of what the branch is about.

   ### Examples:
   * `feature/visa-support`
   * `bug/broken-mastercard-detection`
   * `chore/refactor-validate-function`

   Be concise but descriptive.

4. Commit your changes to the development branch.
5. Make a pull request.

## Releases

### Stable

Latest stable version can always be found in the [master branch](https://github.com/PawelDecowski/jquery-creditcardvalidator/tree/master).

You can find current and previous stable releases on the [releases page](https://github.com/PawelDecowski/jquery-creditcardvalidator/tags).

### Development

There are no development releases. All features, bugs and chores are developed in their own branches of master, then are merged into a release branch (eg release/1.1), which is in turn tagged and merged into master. Then the cycle repeats.
