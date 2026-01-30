<?php
use Illuminate\Support\Facades\Session;

function currentUser() {

    return Session::get('user');
}

