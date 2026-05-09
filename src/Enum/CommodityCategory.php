<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express commodity category codes for landed-cost and export-declaration line items.
 *
 * Sourced from the `commodityCategory` sheet of `dhl_reference_data.xlsx`
 * (108 codes). The backing value is the integer code used on the wire.
 *
 * Codes are grouped by category:
 * - Apparel (101–116)
 * - Footwear (201–204)
 * - Eyewear (301–304)
 * - Watches / Jewelry / Bags (401–406)
 * - Alcohol (501–504)
 * - Soft drinks (601–606)
 * - Food (701–712)
 * - Tobacco (801–804)
 * - Cleaning products (901–905)
 * - Personal care (1001–1004)
 * - Paper / hygiene (1101–1106)
 * - Electronics (1202–1206)
 * - Large appliances (1301–1315)
 * - Furniture (1401–1408)
 * - Health / pharma (1501–1506)
 * - Toys / Sports (1601–1603)
 */
enum CommodityCategory: int
{
    // Apparel (101–116)
    case CoatsAndJackets = 101;
    case Blazers = 102;
    case Suits = 103;
    case Ensembles = 104;
    case Trousers = 105;
    case ShirtsAndBlouses = 106;
    case Dresses = 107;
    case Skirts = 108;
    case JerseysSweatshirtsPullovers = 109;
    case SportsAndSwimwear = 110;
    case NightAndUnderwear = 111;
    case TShirts = 112;
    case TightsAndLeggings = 113;
    case Socks = 114;
    case BabyClothes = 115;
    case ClothingAccessories = 116;

    // Footwear (201–204)
    case Sneakers = 201;
    case AthleticFootwear = 202;
    case LeatherFootwear = 203;
    case TextileAndOtherFootwear = 204;

    // Eyewear (301–304)
    case SpectacleLenses = 301;
    case Sunglasses = 302;
    case EyewearFrames = 303;
    case ContactLenses = 304;

    // Watches / Jewelry / Bags (401–406)
    case Watches = 401;
    case Jewelry = 402;
    case SuitcasesAndBriefcases = 403;
    case Handbags = 404;
    case WalletsAndLittleCases = 405;
    case BagsAndContainers = 406;

    // Alcohol (501–504)
    case Beer = 501;
    case Spirits = 502;
    case Wine = 503;
    case CiderPerryRiceWine = 504;

    // Soft drinks (601–606)
    case BottledWater = 601;
    case SoftDrinks = 602;
    case Juices = 603;
    case Coffee = 604;
    case Tea = 605;
    case Cocoa = 606;

    // Food (701–712)
    case DairyProductsAndEggs = 701;
    case Meat = 702;
    case FishAndSeafood = 703;
    case FruitsAndNuts = 704;
    case Vegetables = 705;
    case BreadAndCerealProducts = 706;
    case OilsAndFats = 707;
    case SaucesAndSpices = 708;
    case ConvenienceFood = 709;
    case SpreadsAndSweeteners = 710;
    case BabyFood = 711;
    case PetFood = 712;

    // Tobacco (801–804)
    case Cigarettes = 801;
    case SmokingTobacco = 802;
    case Cigars = 803;
    case ECigarettes = 804;

    // Cleaning products (901–905)
    case HouseholdCleaners = 901;
    case DishwashingDetergents = 902;
    case Polishes = 903;
    case RoomScents = 904;
    case Insecticides = 905;

    // Personal care (1001–1004)
    case Cosmetics = 1001;
    case SkinCare = 1002;
    case PersonalCare = 1003;
    case Fragrances = 1004;

    // Paper / hygiene (1101–1106)
    case ToiletPaper = 1101;
    case PaperTissues = 1102;
    case HouseholdPaper = 1103;
    case FeminineHygiene = 1104;
    case BabyDiapers = 1105;
    case Incontinence = 1106;

    // Electronics (1202–1206)
    case TvRadioMultimedia = 1202;
    case TvPeripheralDevices = 1203;
    case Telephony = 1204;
    case Computing = 1205;
    case Drones = 1206;

    // Large appliances (1301–1315)
    case Refrigerators = 1301;
    case Freezers = 1302;
    case DishwashingMachines = 1303;
    case WashingMachines = 1304;
    case CookersAndOven = 1305;
    case VacuumCleaners = 1306;
    case SmallKitchenAppliances = 1307;
    case HairClippers = 1308;
    case Irons = 1309;
    case Toasters = 1310;
    case GrillsAndRoasters = 1311;
    case HairDryers = 1312;
    case CoffeeMachines = 1313;
    case MicrowaveOvens = 1314;
    case ElectricKettles = 1315;

    // Furniture (1401–1408)
    case SeatsAndSofas = 1401;
    case Beds = 1402;
    case Mattresses = 1403;
    case ClosetsNightstandsDressers = 1404;
    case LampsAndLighting = 1405;
    case FloorCovering = 1406;
    case KitchenFurniture = 1407;
    case PlasticAndOtherFurniture = 1408;

    // Health / pharma (1501–1506)
    case Analgesics = 1501;
    case ColdAndCoughRemedies = 1502;
    case DigestivesAndIntestinalRemedies = 1503;
    case SkinTreatment = 1504;
    case VitaminsAndMinerals = 1505;
    case HandSanitizer = 1506;

    // Toys / Sports (1601–1603)
    case ToysAndGames = 1601;
    case MusicalInstruments = 1602;
    case SportsEquipment = 1603;
}
