<?php
/**
 * amadeus-ws-client
 *
 * Copyright 2015 Amadeus Benelux NV
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package Amadeus
 * @license https://opensource.org/licenses/Apache-2.0 Apache 2.0
 */

namespace Amadeus\Client\Struct\PriceXplorer;

use Amadeus\Client\RequestOptions\PriceXplorerExtremeSearchOptions;
use Amadeus\Client\Struct\BaseWsMessage;

/**
 * ExtremeSearch
 *
 * @package Amadeus\Client\Struct\PriceXplorer
 * @author Dieter Devlieghere <dieter.devlieghere@benelux.amadeus.com>
 */
class ExtremeSearch extends BaseWsMessage
{
    /**
     * Itinerary information group
     *
     * @var ItineraryGrp[]
     */
    public $itineraryGrp = [];

    /**
     * Budget info
     *
     * @var Budget
     */
    public $budget;

    /**
     * Departure dates ranges
     *
     * @var TravelDates
     */
    public $travelDates;

    /**
     * Stay duration and flexibility
     *
     * @var StayDuration
     */
    public $stayDuration;

    /**
     * Attribute Information
     *
     * @var AttributeInfo[]
     */
    public $attributeInfo = [];

    /**
     * Option description : Price result distribution, ...
     *
     * @var SelectionDetailsGroup[]
     */
    public $selectionDetailsGroup = [];

    /**
     * List of departure days
     *
     * @var DepartureDays[]
     */
    public $departureDays = [];

    /**
     * Airline information
     *
     * @var AirlineInfo[]
     */
    public $airlineInfo = [];

    /**
     * List of Office Id Details
     *
     * @var OfficeIdInfo[]
     */
    public $officeIdInfo = [];

    /**
     * Construct PriceXplorer_ExtremeSearch Request message
     *
     * @param PriceXplorerExtremeSearchOptions $params
     */
    public function __construct(PriceXplorerExtremeSearchOptions $params)
    {
        $this->itineraryGrp[] = new ItineraryGrp($params->origin);

        if ($params->earliestDepartureDate instanceof \DateTime || $params->latestDepartureDate instanceof \DateTime) {
            $this->travelDates = new TravelDates($params->earliestDepartureDate, $params->latestDepartureDate);
        }

        if ($params->searchOffice !== null) {
            $this->officeIdInfo[] = new OfficeIdInfo($params->searchOffice);
        }

        if (($params->maxBudget !== null || $params->minBudget !== null) && $params->currency !== null) {
            $this->budget = new Budget(
                $params->maxBudget,
                $params->minBudget,
                $params->currency
            );
        }

        if (!empty($params->destinations)) {
            foreach ($params->destinations as $destination) {
                $this->itineraryGrp[] = new ItineraryGrp(null, $destination);
            }
        }

        if (!empty($params->destinationCountries)) {
            foreach ($params->destinationCountries as $destCountry) {
                $tmpGrp = new ItineraryGrp();

                $tmpGrp->locationInfo = new LocationInfo(LocationInfo::LOC_COUNTRY);

                $tmpGrp->locationInfo->locationDescription = new LocationIdentificationType();
                $tmpGrp->locationInfo->locationDescription->qualifier = LocationIdentificationType::QUAL_DESTINATION;
                $tmpGrp->locationInfo->locationDescription->code = $destCountry;

                $this->itineraryGrp[] = $tmpGrp;
            }
        }

        if (!empty($params->departureDaysInbound)) {
            $this->departureDays[] = new DepartureDays($params->departureDaysInbound, SelectionDetails::OPT_INBOUND_DEP_DAYS);
        }
        if (!empty($params->departureDaysOutbound)) {
            $this->departureDays[] = new DepartureDays(
                $params->departureDaysOutbound,
                SelectionDetails::OPT_OUTBOUND_DEP_DAYS
            );
        }

        if ($params->stayDurationDays !== null) {
            $this->stayDuration = new StayDuration($params->stayDurationDays);

            if ($params->stayDurationFlexibilityDays !== null) {
                $this->stayDuration->flexibilityInfo = new FlexibilityInfo($params->stayDurationFlexibilityDays);
            }
        }

        if ($params->returnCheapestNonStop || $params->returnCheapestOverall) {

            $tmpSelDet = new SelectionDetailsGroup();

            $tmpSelDet->selectionDetailsInfo = new SelectionDetailsInfo();
            $tmpSelDet->selectionDetailsInfo->selectionDetails[] = new SelectionDetails(
                SelectionDetails::OPT_PRICE_RESULT_DISTRIBUTION
            );

            $tmpSelDet->nbOfUnitsInfo = new NbOfUnitsInfo();

            if ($params->returnCheapestNonStop) {

                $tmpSelDet->nbOfUnitsInfo->quantityDetails[] = new NumberOfUnitDetailsType(
                    null,
                    NumberOfUnitDetailsType::QUAL_CHEAPEST_NONSTOP
                );
            }

            if ($params->returnCheapestOverall) {

                $tmpSelDet->nbOfUnitsInfo->quantityDetails[] = new NumberOfUnitDetailsType(
                    null,
                    NumberOfUnitDetailsType::QUAL_CHEAPEST_OVERALL
                );
            }

            $this->selectionDetailsGroup[] = $tmpSelDet;
        }

        if ($params->resultAggregationOption !== null) {
            $groupTypes = $this->makeAggregationGroupTypes($params->resultAggregationOption);

            $tmpAttrInfo = new AttributeInfo(
                AttributeInfo::FUNC_GROUPING,
                $groupTypes
            );

            $this->attributeInfo[] = $tmpAttrInfo;
        }
    }

    /**
     * @param string $groupTypeString
     * @return array
     */
    protected function makeAggregationGroupTypes($groupTypeString)
    {
        $result = [];

        switch($groupTypeString) {
            case PriceXplorerExtremeSearchOptions::AGGR_DEST:
                $result[] = AttributeDetails::TYPE_DESTINATION;
                break;
            case PriceXplorerExtremeSearchOptions::AGGR_COUNTRY:
                $result[] = AttributeDetails::TYPE_COUNTRY;
                break;
            case PriceXplorerExtremeSearchOptions::AGGR_DEST_WEEK:
                $result[] = AttributeDetails::TYPE_DESTINATION;
                $result[] = AttributeDetails::TYPE_WEEK;
                break;
            case PriceXplorerExtremeSearchOptions::AGGR_DEST_WEEK_DEPART:
                $result[] = AttributeDetails::TYPE_DESTINATION;
                $result[] = AttributeDetails::TYPE_WEEK;
                $result[] = AttributeDetails::TYPE_DEPARTURE_DAY;
                break;
            case PriceXplorerExtremeSearchOptions::AGGR_DEST_WEEK_DEPART_STAY:
                $result[] = AttributeDetails::TYPE_DESTINATION;
                $result[] = AttributeDetails::TYPE_WEEK;
                $result[] = AttributeDetails::TYPE_DEPARTURE_DAY;
                $result[] = AttributeDetails::TYPE_STAY_DURATION;
                break;
        }

        return $result;
    }
}
