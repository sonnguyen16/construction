<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Contractor;
use App\Models\Project;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contractor_id',
        'project_id',
        'amount',
        'start_date',
        'end_date',
        'interest_rate',
        'status',
        'contract_file',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
    ];

    // Quan hệ với nhà cung cấp/bên cho vay
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
    
    // Quan hệ với dự án
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Quan hệ với người tạo
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Quan hệ với người cập nhật
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
