<?php

namespace Onefile\Models;

use Onefile\Exceptions\NotFoundHttpException;

class Assessor extends Model
{
    /**
     * 4 = Trainee Assessor
     * 5 = Assessor
     *
     * @var array
     */
    protected $roles = [4, 5];

    /**
     * @var array
     */
    protected $uris = [
        'root' => 'User',
        'search' => 'User/Search'
    ];

    /**
     * Assessor constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $firstName
     * @param $surname
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function findByNames($firstName, $surname)
    {
        $user = collect($this->search([
            'OrganisationID' => $this->centreId,
            'FirstName' => $firstName,
            'LastName' => $surname,
        ]));
        
        if ($user->count() > 0) {
            return $user->first();
        }

        throw new NotFoundHttpException('The requested user could not be found in this centre with a role of Assessor or Trainee Assessor');
    }

    /**
     * @param $params
     * @return mixed
     */
    public function search($params)
    {
        return tap(collect(), function ($assessors) use ($params) {
            collect($this->roles)->each(function ($role) use ($assessors, $params) {
                $params = array_merge(['Role' => $role], $params);
                collect($this->onefile->search($this->uris['search'], $params))->each(function ($user) use ($assessors
                ) {
                    if ($user != '') {
                        $assessors->push($user);
                    }
                });
            });
        });
    }
}