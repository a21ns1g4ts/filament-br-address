<?php

namespace A21ns1g4ts\FilamentBrAddress\Models;

use A21ns1g4ts\FilamentBrAddress\Concerns\HasAddresses;
use Illuminate\Database\Eloquent\Model;

abstract class AddressableModel extends Model
{
    use HasAddresses;
}
