<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpTermTaxonomy extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'term_taxonomy';
    protected $primaryKey = 'term_taxonomy_id';
    public    $timestamps = false;

    protected $fillable = ['term_id', 'taxonomy', 'description', 'parent', 'count'];

    public function term()
    {
        return $this->belongsTo(WpTerm::class, 'term_id', 'term_id');
    }
}
