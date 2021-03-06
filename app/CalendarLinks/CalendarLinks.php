<?php
/*
 * dienstplan
 *
 * Copyright (c) 2019 Christoph Fischer, https://christoph-fischer.org
 * Author: Christoph Fischer, chris@toph.de
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\CalendarLinks;

class CalendarLinks
{

    public static function all() {
        $calendarLinks = [];
        foreach (glob(app_path('CalendarLinks').'/*CalendarLink.php') as $file) {
            if (substr(pathinfo($file, PATHINFO_FILENAME), 0, 8) !== 'Abstract') {
                $calendarLinkClass = 'App\\CalendarLinks\\'.pathinfo($file, PATHINFO_FILENAME);
                if (class_exists($calendarLinkClass)) {
                    /** @var AbstractCalendarLink $calendarLink */
                    $calendarLink = new $calendarLinkClass();
                    $calendarLinks[$calendarLink->getTitle()] = $calendarLink;
                }
            }
        }
        ksort($calendarLinks);
        return $calendarLinks;
    }

    public static function findKey($key) {
        foreach (self::all() as $item) {
            if ($item->getKey()==$key) return $item;
        }
    }
}
