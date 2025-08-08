<?php

namespace App\Responses;

use App\Contracts\ResponseContract;

class ServiceResponse implements ResponseContract {
    /**
     * Setter of response service
     *
     * @param $result
     * @param string $message
     * @param int $status
     */
    public function __construct(public $result, public string $message, public bool $status = true, public string $state) {
        $this->status  = $status;
        $this->message = $message;
        $this->result  = $result;
        $this->state   = $state;
    }

    public function status(): bool {
        return $this->status;
    }

    public function message(): string {
        return $this->message;
    }

    public function result() {
        return $this->result;
    }

    public function state() {
        return $this->state;
    }
}
