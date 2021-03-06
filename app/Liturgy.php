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

namespace App;

use Illuminate\Support\Facades\Cache;

class Liturgy
{

    /** @var Liturgy|null Instance */
    protected static $instance = null;

    public static function getInstance() {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    /**
     * Get all the liturgical info for a given day
     * @param string|\App\Day $day
     * @param bool $fallback
     * @return array
     */
    public static function getDayInfo($day, $fallback = false): array {
        if (!is_object($day)) $day = new Day(['date' => $day]);
        if (!Cache::has('liturgicalDays')) {
            $tmpData = json_decode(
                file_get_contents(
                    'https://www.kirchenjahr-evangelisch.de/service.php?o=lcf&f=gaa&r=json&dl=user'),
                true);
            foreach ($tmpData['content']['days'] as $key => $val) {
                $data[$val['date']] = $val;
            }
            Cache::put('liturgicalDays', $data, 86400);
        } else {
            $data = Cache::get('liturgicalDays');
        }

        if (isset($data[$day->date->format('d.m.Y')])) {
            return $data[$day->date->format('d.m.Y')];
        } elseif ($fallback) {
            $date = $day->date;
            while (!isset($data[$date->format('d.m.Y')])) {
                $date = $date->subDays(1);
            }
            return isset($data[$date->format('d.m.Y')]) ? $data[$date->format('d.m.Y')] : [];
        }
        return [];
    }


}
