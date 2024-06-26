<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{

    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'assists';

    protected $perPage = 20;

    protected $fillable = [
        'id',
        'iclock_transaction_id',
        'punch_time',
        'upload_time',
        'emp_code',
        'dept_name',
        'created_at',
    ];
}
