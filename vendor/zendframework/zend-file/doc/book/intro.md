# Introduction

zend-file provides two specific pieces of functionality:

- a `ClassFileLocator`, which can be used to find PHP class files under a given
  tree.
- a `Transfer` subcomponent, for managing file uploads and reporting upload
  progress.

**The `Transfer` subcomponent is deprecated**, and we recommend using the
file-related functionality in:

- [zend-filter](https://zendframework.github.io/zend-filter/), which provides
  functionality around moving uplaoded files to their final locations, renaming
  uploaded files, and encrypting and decrypting uploaded files.
- [zend-validator](https://github.com/zendframework/zend-validator/), which
  provides functionality around validating uploaded files based on: number of
  files uploaded, MIME types and/or extensions, upload status, compression,
  hashing, and more.
- [zend-progressbar](https://github.com/zendframework/zend-progressbar/), which
  provides functionality for providing file upload status.

If you are determined to use the `Transfer` subcomponent, despite its
deprecation, please see the [Zend Framework 1 documentation on the component](http://framework.zend.com/manual/1.12/en/zend.file.transfer.introduction.html);
you can substitute `Underscore_Separated_Names` for their namespaced equivalents
to adapt the examples to this component.
