Contributing
===
So you found a bug? Or perhaps you just want to improve something?

Validate.js welcomes patches and features! There are however a few things that
are required before your pull request can be merged.

Tests
---
Regardless if it's a bug fix or a feature, your code must be tested.

The tests are automatically run when opening a pull request but to make sure
they don't fail you can run the tests by running this command:

```
$ grunt test
```

Code style
---
Your code should match the overall code style of the project. Running `grunt test`
will also check your code against JSHint.

Building
---
Please **don't** commit changes to `validate.min.js`, `validate.min.map` or
`docs/`

These files are only updated when a new release is produced.

Squashing
---
To make the project bisect safe all features should be committed in single commit.

Please feel free to commit several times while developing but make sure to
[squash](http://gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html)
your commits into one before opening a pull request.

Updating the readme
---
If there is a new case that you feel should be shown in the examples please feel
free to add it.

This is however not required and will be done by a maintainer if needed.
