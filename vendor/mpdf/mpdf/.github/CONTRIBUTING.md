Contributing
============

Issue tracker
-------------

The Issue tracker serves mainly as a place to report bugs and request new features.
Please do not abuse it as a general questions or troubleshooting location.

For these questions you can always use the
[mpdf tag](https://stackoverflow.com/questions/tagged/mpdf) at [Stack Overflow](https://stackoverflow.com/).

* Please provide a small example in php/html that reproduces your situation
* Please report one feature or one bug per issue
* Failing to provide necessary information or not using the issue template may cause the issue to be closed without consideration.

Pull requests
-------------

Pull requests should be always based on the default [development](https://github.com/mpdf/mpdf/tree/development)
branch except for backports to older versions.

Some guidelines:

* Use an aptly named feature branch for the Pull request.

* Only files and lines affecting the scope of the Pull request must be affected.

* Make small, *atomic* commits that keep the smallest possible related code changes together.

* Code should be accompanied by a unit test testing expected behaviour.

When updating a PR, do not create a new one, just `git push --force` to your former feature branch, the PR will
update itself.
