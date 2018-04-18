# Introduction

zend-paginator is a flexible component for paginating collections of data and
presenting that data to users.

The primary design goals of zend-paginator are as follows:

- Paginate arbitrary data, not just relational databases.
- Fetch only the results that need to be displayed.
- Do not force users to adhere to only one way of displaying data or rendering
  pagination controls.
- Loosely couple to other Zend Framework components so that users who wish to
  use it independently of zend-view, zend-Db`, etc. can do so.
