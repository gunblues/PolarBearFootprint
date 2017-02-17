<?php

class ErrorCodeModel {
   const REQUEST_METHOD_MUST_BE_POST = 100;
   const CONTENT_TYPE_MUST_BE_APPLICATION_JSON = 101;
   const RECEIVED_CONTENT_CONTAINED_INVALID_JSON = 102;
   const JSON_REQUIRED_KEY = 103;
   const JSON_DATA_TYPE_INVALID = 104;

   static public $message = [
       self::REQUEST_METHOD_MUST_BE_POST => 'Request method must be POST!',
       self::CONTENT_TYPE_MUST_BE_APPLICATION_JSON => 'Content type must be:application/json',
       self::RECEIVED_CONTENT_CONTAINED_INVALID_JSON => 'Received content contained invalid JSON!',
       self::JSON_REQUIRED_KEY => 'Json required key ',
       self::JSON_DATA_TYPE_INVALID => 'Data type invalid '
   ];
}

// vim: expandtab softtabstop=4 tabstop=4 shiftwidth=4 ts=4 sw=4
