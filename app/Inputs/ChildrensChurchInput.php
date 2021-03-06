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

namespace App\Inputs;

use App\City;
use App\Day;
use App\Mail\ServiceUpdated;
use App\Service;
use App\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChildrensChurchInput extends AbstractInput
{

    public $title = 'Kinderkirche';

    public function canEdit(): bool
    {
        return Auth::user()->can('gd-kinderkirche-bearbeiten');
    }

    public function input(Request $request) {
        $request->validate([
            'year' => 'int|required',
            'city' => 'int|required',
        ]);

        $city = City::find($request->get('city'));
        $year = $request->get('year');

        $services = Service::with('day', 'location')
            ->select('services.*')
            ->join('days', 'services.day_id', '=', 'days.id')
            ->where('city_id', $city->id)
            ->where('days.date', '>=', $year.'-01-01')
            ->where('days.date', '<=', $year.'-12-31')
            ->orderBy('days.date', 'ASC')
            ->orderBy('time', 'ASC')
            ->get();



        $input = $this;
        return view($this->getInputViewName(), compact('input', 'city', 'services', 'year'));

    }

    public function save(Request $request) {
        $services = $request->get('service') ?: [];
        foreach ($services as $id => $data) {
            $service = Service::find($id);
            if (null !== $service) {

                // get old data set for comparison
                $original = clone $service;
                foreach (['P', 'O', 'M', 'A'] as $key) {
                    $originalParticipants[$key] = $original->participantsText($key);
                }


                if (isset($data['cc']) ? $data['cc'] : 0) {
                    if (!is_object($service->location)) {
                        $ccLocation = isset($data['cc_location']) ? $data['cc_location'] : '';
                    } else {
                        $ccLocation = isset($data['cc_location']) ? $data['cc_location'] : $service->location->cc_default_location;
                    }
                } else {
                    $ccLocation = '';
                }

                $service->cc = isset($data['cc']) ? 1: 0;
                $service->cc_location = $ccLocation;
                $service->cc_lesson = $data['cc_lesson'] ?: '';
                $service->cc_staff = $data['cc_staff'] ?: '';
                $dirty = (count($service->getDirty()) > 0);
                $service->save();

                if ($dirty) {
                    Subscription::send($service, ServiceUpdated::class, compact('original', 'originalParticipants'));
                }

            }
        }
        return redirect()->route('calendar')->with('success', 'Der Plan für die Kinderkirche wurde gespeichert.');
    }


}
