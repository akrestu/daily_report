<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Transisi status tiket yang tidak valid. Pesannya aman ditampilkan ke pengguna.
 */
class TicketWorkflowException extends RuntimeException {}
