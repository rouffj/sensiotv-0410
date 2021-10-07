<?php

namespace App\EventListener;

use App\Entity\Movie;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class MovieListener
{
    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->notifyUsersOnMovieImported($event->getObject());
        // Put your logic here before or after a DB change occured inside an entity like:
        // - Log column change
        // - Send a notification / email each time an entity is added into DB
        // - ...
    }

    private function notifyUsersOnMovieImported(Movie $movie)
    {
        $email = [
            'from' => 'admin@sensiotv.io',
            'to' => 'users@sensiotv.io',
            'subject' => 'A new movie '. $movie->getTitle().' has been imported',
            'content' => 'See it with this link http://localhost:8003/movie/' . $movie->getId()
        ];

        dump($email);
    }
}