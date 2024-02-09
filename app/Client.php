<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    
    public function vouchers() {
        return $this->hasMany(Voucher::class);
    }

    public function document_type() {
        return $this->belongsTo(DocumentType::class);
    }

    public function link_client() {
        return $this->belongsTo(Client::class);
    }

    public function perception_percentage() {
        return $this->belongsTo(Rate::class);
    }

    public function client_addresses() {
        return $this->hasMany(ClientAddress::class);
    }

    public function client_zone() {
        return $this->belongsTo(ClientZone::class, 'zone_id');
    }

    public function client_channel() {
        return $this->belongsTo(ClientChannel::class, 'channel_id');
    }

    public function client_route() {
        return $this->belongsTo(ClientRoute::class, 'route_id');
    }

    public function client_sector() {
        return $this->belongsTo(ClientSector::class, 'sector_id');
    }
}
