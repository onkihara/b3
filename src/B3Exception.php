<?php

namespace Onkihara\B3;

use Exception;
use Illuminate\Http\Request;
use League\Flysystem\FilesystemException;

class B3Exception extends Exception implements FilesystemException {


}