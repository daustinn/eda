<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasUuids;

    protected $table = 'job_positions';

    protected $perPage = 20;

    protected $fillable = [
        'code',
        'level',
        'name',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    static $rules = [
        'name' => 'required',
        'code' => 'required',
        'level' => ['required', 'numeric'],
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'id', 'updated_by');
    }
}
