<?php

    namespace Common\Connection;

    abstract class Connection
    {

        protected $connection = null;

        abstract public function getConnection();
    }