<?php

namespace Exception;

interface HttpExceptionInterface
{

	public function getStatusCode();

    public function getHeaders();
}
