<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LucaCermenati\CommonTraits\Traits\HasAttachments;

class Document extends Model
{
    use HasAttachments;

    protected $table = 'documents';

    protected $guarded = [];
}
