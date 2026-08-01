<?php

namespace App\Services\Web;

use App\Models\SchoolLaw;
use Illuminate\Database\Eloquent\Collection;

class SchoolLawService
{

    public function getAllLaws(): Collection
    {
        return SchoolLaw::latest()->get();
    }

    public function createLaw(array $data): SchoolLaw
    {
        return SchoolLaw::create($data);
    }


    public function updateLaw(SchoolLaw $schoolLaw, array $data): SchoolLaw
    {
        $schoolLaw->update($data);
        return $schoolLaw;
    }


    public function deleteLaw(SchoolLaw $schoolLaw): void
    {
        $schoolLaw->delete();
    }
}
