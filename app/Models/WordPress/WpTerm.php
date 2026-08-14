<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpTerm extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'terms';
    protected $primaryKey = 'term_id';
    public    $timestamps = false;

    protected $fillable = ['name', 'slug', 'term_group'];

    public function taxonomy()
    {
        return $this->hasOne(WpTermTaxonomy::class, 'term_id', 'term_id');
    }

    public function meta()
    {
        return $this->hasMany(WpTermMeta::class, 'term_id', 'term_id');
    }

    public function getMeta(string $key, $default = null)
    {
        $meta = $this->meta->firstWhere('meta_key', $key);
        return $meta ? $meta->meta_value : $default;
    }

    public function scopeByTaxonomy($query, string $taxonomy)
    {
        return $query->join('term_taxonomy', 'terms.term_id', '=', 'term_taxonomy.term_id')
                     ->where('term_taxonomy.taxonomy', $taxonomy)
                     ->select('terms.*', 'term_taxonomy.description', 'term_taxonomy.parent', 'term_taxonomy.count', 'term_taxonomy.term_taxonomy_id');
    }
}
