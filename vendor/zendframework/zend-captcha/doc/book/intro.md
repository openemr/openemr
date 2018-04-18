# Introduction

[CAPTCHA](http://en.wikipedia.org/wiki/Captcha) stands for "Completely Automated
Public Turing test to tell Computers and Humans Apart"; it is used as a
challenge-response to ensure that the individual submitting information is a
human and not an automated process. Typically, a captcha is used with form
submissions where authenticated users are not necessary, but you want to prevent
spam submissions.

## Overview

CAPTCHAs can take a variety of forms, including asking logic questions,
presenting skewed fonts, and presenting multiple images and asking how they
relate. The `Zend\Captcha` component aims to provide a variety of back ends that
may be utilized either standalone or in conjunction with
[zend-form](https://zendframework.github.io/zend-form/).
