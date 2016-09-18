# Doctrine CouchDB Client

[![Build Status](https://travis-ci.org/doctrine/couchdb-client.png?branch=master)](https://travis-ci.org/doctrine/couchdb-client)

Simple API that wraps around CouchDBs HTTP API.

## Features

* Create, Delete Databases
* Create, Update, Delete Documents
* Bulk API for Creating/Updating Documents
* Find Documents by ID
* Generate UUIDs
* Query `_all_docs` view
* Query Changes Feed
* Compaction Info and Triggering APIs
* Replication API
* Symfony Console Commands

## Installation

With Composer:

    {
        "require": {
            "doctrine/couchdb": "@dev"
        }
    }

## Usage

```php
<?php
$client = \Doctrine\CouchDB\CouchDBClient::create();

array($id, $rev) = $client->postDocument(array('foo' => 'bar'));
$client->putDocument(array('foo' => 'baz'), $id, $rev);

$doc = $client->findDocument($id);
```
