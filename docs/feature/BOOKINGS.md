# Bookings Feature Documentation
_Last changed: 25th January 2020_

## Overview
UKTS facilitates bookings on positions which within the database. There is a fair amount of logic involved in making a booking, the documentation of which is listed below. 
Bookings within UKTS for the same position cannot overlap. 

## Rating
VATSIM GRP dictates that members with specific ATC ratings only control certain positions. For example, an S2 rated member cannot control an approach / TMA position. UKTS evaluates the suffix of the proposed booking position, e.g. APP and uses this to determine the rating requirements. The central authentication service is queried, and returns the details of the user, along with their rating. This rating is then compared with the specification in this API, thus determining if that user can book the position.

## Special Endorsements
VATSIM GRP dictates that divisions can restrict the usage of positions by designating them as ‘special positions’ which require additional training/experience beyond what has occurred during regular ATC training. Therefore UKTS tracks any of these designations made by the division and validates that should an endorsement be required for a position; it checks that the user has said endorsement. 
