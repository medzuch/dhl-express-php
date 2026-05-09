<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Enum;

/**
 * DHL Express Value Added Service codes.
 *
 * Sourced from the `serviceCode` sheet of `dhl_reference_data.xlsx`
 * (384 unique codes). The backing value is the exact wire code.
 *
 * Cases whose wire code begins with a digit use a descriptive PascalCase
 * name. Cases with all-letter codes use the descriptive name from the
 * reference workbook.
 *
 * Duplicate code TZ (DirectToServicePoint) is represented once.
 *
 * @see \Medzuch\DhlExpress\Dto\Shipment\ValueAddedService
 */
enum ServiceCode: string
{
    /** 0G — Local Distribution Centre */
    case LocalDistributionCentre = '0G';
    /** WH — Customs Physical Intervention */
    case CustomsPhysicalIntervention = 'WH';
    /** WJ — Permits And Licenses */
    case PermitsAndLicenses = 'WJ';
    /** AB — Saturday Pickup */
    case SaturdayPickup = 'AB';
    /** JD — Verbal Notification */
    case VerbalNotification = 'JD';
    /** PV — Data Staging 12 */
    case DataStaging12 = 'PV';
    /** IC — Shipment Insurance IC */
    case ShipmentInsuranceIC = 'IC';
    /** HY — Biological Substance */
    case BiologicalSubstance = 'HY';
    /** UE — Ambient Extreme */
    case AmbientExtreme = 'UE';
    /** UF — Chilled Box S */
    case ChilledBoxS = 'UF';
    /** UK — Frozen No Ice L */
    case FrozenNoIceL = 'UK';
    /** 97 — Local Charge 97 */
    case LocalCharge97 = '97';
    /** HO — Newspapers */
    case Newspapers = 'HO';
    /** WT — Sale In Transit */
    case SaleInTransit = 'WT';
    /** ZF — Promotional Discount */
    case PromotionalDiscount = 'ZF';
    /** YU — DHL Parcel Shipment YU */
    case DhlParcelShipmentYU = 'YU';
    /** CK — Secure Handling Process */
    case SecureHandlingProcess = 'CK';
    /** PP — Neutral Description Label */
    case NeutralDescriptionLabel = 'PP';
    /** YH — ACS Child */
    case AcsChild = 'YH';
    /** GP — DHL Express Polybag */
    case DhlExpressPolybag = 'GP';
    /** FT — Go Green Plus FT */
    case GoGreenPlusFT = 'FT';
    /** 3L — Lithium Ion Batteries 3L */
    case LithiumIonBatteries3L = '3L';
    /** 3N — Biological Substance Cat B */
    case BiologicalSubstanceCatB = '3N';
    /** 3S — Expected Packages Radioactive */
    case ExpectedPackagesRadioactive = '3S';
    /** 3U — Radio Active Material */
    case RadioActiveMaterial = '3U';
    /** 3Z — Company Mail */
    case CompanyMail = '3Z';
    /** 4B — Heavy Cargo */
    case HeavyCargo = '4B';
    /** 4J — Flammable Liquid */
    case FlammableLiquid = '4J';
    /** 51 — Two Person Delivery */
    case TwoPersonDelivery = '51';
    /** 44 — Time Window Delivery 1517 */
    case TimeWindowDelivery1517 = '44';
    /** 45 — Time Window Delivery 1719 */
    case TimeWindowDelivery1719 = '45';
    /** TO — Leave In Safe Place */
    case LeaveInSafePlace = 'TO';
    /** 4Z — Combustion Engine Vehicle */
    case CombustionEngineVehicle = '4Z';
    /** WY — Paperless Trade */
    case PaperlessTrade = 'WY';
    /** PQ — Personally Identifiable Data */
    case PersonallyIdentifiableData = 'PQ';
    /** GB — Laptop Box */
    case LaptopBox = 'GB';
    /** PA — Shipment Preparation */
    case ShipmentPreparation = 'PA';
    /** HM — Lithium Metal PI969 Section II */
    case LithiumMetalPI969SectionII = 'HM';
    /** GC — Bottle Box */
    case BottleBox = 'GC';
    /** 95 — Local Charge 95 */
    case LocalCharge95 = '95';
    /** 92 — Local Charge 92 */
    case LocalCharge92 = '92';
    /** 90 — Local Charge 90 */
    case LocalCharge90 = '90';
    /** SE — Contract Signature */
    case ContractSignature = 'SE';
    /** UB — Ambient Vialsafe */
    case AmbientVialsafe = 'UB';
    /** UD — Ambient Insulated */
    case AmbientInsulated = 'UD';
    /** HV — Lithium Ion PI967 Section II */
    case LithiumIonPI967SectionII = 'HV';
    /** PK — PLT Images Pending */
    case PltImagesPending = 'PK';
    /** PO — Comat */
    case Comat = 'PO';
    /** CG — Diplomatic Material */
    case DiplomaticMaterial = 'CG';
    /** 3E — General Cargo */
    case GeneralCargo = '3E';
    /** 4O — Toxic Substance */
    case ToxicSubstance = '4O';
    /** 55 — VIP Service */
    case VipService = '55';
    /** 33 — Third Country International */
    case ThirdCountryInternational = '33';
    /** HC — Dry Ice UN1845 */
    case DryIceUN1845 = 'HC';
    /** PX — Non Standard Pickup */
    case NonStandardPickup = 'PX';
    /** PD — Data Entry */
    case DataEntry = 'PD';
    /** QH — Bypass Injection */
    case BypassInjection = 'QH';
    /** YW — Breakbulk Mother */
    case BreakbulkMother = 'YW';
    /** VE — Pressure Bag S */
    case PressureBagS = 'VE';
    /** TY — Pre 1200 */
    case Pre1200 = 'TY';
    /** UI — Frozen No Ice S */
    case FrozenNoIceS = 'UI';
    /** UO — Frozen Ice Plates S */
    case FrozenIcePlatesS = 'UO';
    /** HP — Pharmaceuticals */
    case Pharmaceuticals = 'HP';
    /** HQ — Vulnerable Cargo */
    case VulnerableCargo = 'HQ';
    /** SF — Direct Signature */
    case DirectSignature = 'SF';
    /** X1 — Tax Stamp */
    case TaxStamp = 'X1';
    /** BH — Tracked */
    case Tracked = 'BH';
    /** LT — Military Licensed Items */
    case MilitaryLicensedItems = 'LT';
    /** PJ — Automated Digital Imaging */
    case AutomatedDigitalImaging = 'PJ';
    /** CX — Compliance Monitored Account */
    case ComplianceMonitoredAccount = 'CX';
    /** 4E — Obnoxious Cargo */
    case ObnoxiousCargo = '4E';
    /** 4M — Organic Peroxide */
    case OrganicPeroxide = '4M';
    /** 3Q — Lithium Ion Batteries 3Q */
    case LithiumIonBatteries3Q = '3Q';
    /** 50 — Tail Lift Van */
    case TailLiftVan = '50';
    /** 57 — Return Packaging Material */
    case ReturnPackagingMaterial = '57';
    /** UV — Cryogenic Container XL */
    case CryogenicContainerXL = 'UV';
    /** U7 — Ambient Shipper Box S */
    case AmbientShipperBoxS = 'U7';
    /** PI — Trusted Customer Data */
    case TrustedCustomerData = 'PI';
    /** CJ — Onboard Courier */
    case OnboardCourier = 'CJ';
    /** HE — Full Dangerous Goods */
    case FullDangerousGoods = 'HE';
    /** LA — Shipment Intercept */
    case ShipmentIntercept = 'LA';
    /** BC — Untracked Letter */
    case UntrackedLetter = 'BC';
    /** SW — Leave With Neighbour */
    case LeaveWithNeighbour = 'SW';
    /** XE — Merchandise Process */
    case MerchandiseProcess = 'XE';
    /** YX — Loose Bulk DD */
    case LooseBulkDD = 'YX';
    /** XG — Countervailing Duty */
    case CountervailingDuty = 'XG';
    /** LV — Embargo Routing */
    case EmbargoRouting = 'LV';
    /** AX — Saturday Delivery */
    case SaturdayDelivery = 'AX';
    /** PR — Return To Origin */
    case ReturnToOrigin = 'PR';
    /** GH — Easy Green Capsule */
    case EasyGreenCapsule = 'GH';
    /** 3O — DG In Excepted Quantities */
    case DgInExceptedQuantities = '3O';
    /** 4A — Foodstuffs */
    case Foodstuffs = '4A';
    /** 4C — Living Human Organs Blood */
    case LivingHumanOrgansBlood = '4C';
    /** 4W — No Accompanying Paper Airwaybill */
    case NoAccompanyingPaperAirwaybill = '4W';
    /** NB — Demand Oversize Piece */
    case DemandOversizePiece = 'NB';
    /** CT — Unknown Shipper */
    case UnknownShipper = 'CT';
    /** U2 — Cryogenic Container M */
    case CryogenicContainerM = 'U2';
    /** 1A — Handover To Cryo */
    case HandoverToCryo = '1A';
    /** U6 — Large Shipper Box 1525C */
    case LargeShipperBox1525C = 'U6';
    /** 1J — Used Medical Device Box 88995 */
    case UsedMedicalDeviceBox88995 = '1J';
    /** SG — Signature Required */
    case SignatureRequired = 'SG';
    /** 0A — Logistics Services */
    case LogisticsServices = '0A';
    /** GD — Repacking */
    case Repacking = 'GD';
    /** MA — Address Correction */
    case AddressCorrection = 'MA';
    /** SC — Named Signature */
    case NamedSignature = 'SC';
    /** TK — Residential Address */
    case ResidentialAddress = 'TK';
    /** DU — Duty Tax Processing DU */
    case DutyTaxProcessingDU = 'DU';
    /** 0M — Priority Account Desk */
    case PriorityAccountDesk = '0M';
    /** UL — Frozen Ice Sticks S */
    case FrozenIceSticksS = 'UL';
    /** Y3 — Piece Dimension Exemption */
    case PieceDimensionExemption = 'Y3';
    /** WU — Clearance Paperwork */
    case ClearancePaperwork = 'WU';
    /** WW — Duty Tax Processing WW */
    case DutyTaxProcessingWW = 'WW';
    /** 65 — Odd Vacation Hold */
    case OddVacationHold = '65';
    /** 3R — Lithium Metal Batteries 3R */
    case LithiumMetalBatteries3R = '3R';
    /** 4H — Cryogenic Liquids */
    case CryogenicLiquids = '4H';
    /** 4R — Explosive 14D */
    case Explosive14D = '4R';
    /** LI — Walk In Fridge 28C */
    case WalkInFridge28C = 'LI';
    /** 42 — Time Window Delivery 1113 */
    case TimeWindowDelivery1113 = '42';
    /** U1 — Cryogenic Container S */
    case CryogenicContainerS = 'U1';
    /** U9 — Ambient Shipper Box L */
    case AmbientShipperBoxL = 'U9';
    /** 1F — Reusable Shipper Box XL */
    case ReusableShipperBoxXL = '1F';
    /** 1G — Used Medical Device Box EP250 */
    case UsedMedicalDeviceBoxEP250 = '1G';
    /** 30 — Import Destination Billing */
    case ImportDestinationBilling = '30';
    /** WI — Other Government Agency */
    case OtherGovernmentAgency = 'WI';
    /** BI — Untracked Large Letter */
    case UntrackedLargeLetter = 'BI';
    /** CR — Emergency Situation CR */
    case EmergencySituationCR = 'CR';
    /** GG — Packaging Item */
    case PackagingItem = 'GG';
    /** QD — Late Pickup */
    case LatePickup = 'QD';
    /** QI — Direct Injection */
    case DirectInjection = 'QI';
    /** BD — Untracked Packet */
    case UntrackedPacket = 'BD';
    /** VD — Dry Ice Supplies */
    case DryIceSupplies = 'VD';
    /** HK — Consumer Goods Id 8000 */
    case ConsumerGoodsId8000 = 'HK';
    /** PW — Data Staging 24 */
    case DataStaging24 = 'PW';
    /** XB — Import Export Taxes */
    case ImportExportTaxes = 'XB';
    /** UH — Chilled Box L */
    case ChilledBoxL = 'UH';
    /** UJ — Frozen No Ice M */
    case FrozenNoIceM = 'UJ';
    /** UM — Frozen Ice Sticks M */
    case FrozenIceSticksM = 'UM';
    /** UR — Combination No Ice */
    case CombinationNoIce = 'UR';
    /** PG — Border Management */
    case BorderManagement = 'PG';
    /** 3Y — Bulk Unitization Program */
    case BulkUnitizationProgram = '3Y';
    /** P2 — Alcoholic Beverages Tax Declaration */
    case AlcoholicBeveragesTaxDeclaration = 'P2';
    /** 46 — Time Window Delivery 1921 */
    case TimeWindowDelivery1921 = '46';
    /** HD — Lithium Ion PI966 Section II */
    case LithiumIonPI966SectionII = 'HD';
    /** CM — Secure Protection */
    case SecureProtection = 'CM';
    /** CB — Restricted Destination */
    case RestrictedDestination = 'CB';
    /** NN — Neutral Delivery */
    case NeutralDelivery = 'NN';
    /** TA — Dedicated Delivery */
    case DedicatedDelivery = 'TA';
    /** 93 — Local Charge 93 */
    case LocalCharge93 = '93';
    /** XI — Import Penalty */
    case ImportPenalty = 'XI';
    /** ZD — Customer Rebate */
    case CustomerRebate = 'ZD';
    /** LW — Alternative Address */
    case AlternativeAddress = 'LW';
    /** TX — Pre 1030 */
    case Pre1030 = 'TX';
    /** 98 — Local Charge 98 */
    case LocalCharge98 = '98';
    /** YV — DHL Parcel Shipment YV */
    case DhlParcelShipmentYV = 'YV';
    /** XJ — Trade Zone Process */
    case TradeZoneProcess = 'XJ';
    /** HR — Human Remains */
    case HumanRemains = 'HR';
    /** XZ — Regulatory Fee */
    case RegulatoryFee = 'XZ';
    /** XV — Customs Services VAT */
    case CustomsServicesVat = 'XV';
    /** RB — Freight Services RB */
    case FreightServicesRB = 'RB';
    /** XA — Additional Duty */
    case AdditionalDuty = 'XA';
    /** WZ — Customs Clearance Report */
    case CustomsClearanceReport = 'WZ';
    /** KI — Currency Effects */
    case CurrencyEffects = 'KI';
    /** PF — Mail Service Process PF */
    case MailServiceProcessPF = 'PF';
    /** YG — ACS Master */
    case AcsMaster = 'YG';
    /** 3C — Lithium Ion Metal B3C */
    case LithiumIonMetalB3C = '3C';
    /** 3F — Dry Ice 3F */
    case DryIce3F = '3F';
    /** 3K — Fish Seafood */
    case FishSeafood = '3K';
    /** 3M — Lithium Metal Batteries 3M */
    case LithiumMetalBatteries3M = '3M';
    /** 3T — Radio Active Material Cat I */
    case RadioActiveMaterialCatI = '3T';
    /** 56 — Doorstep Swap */
    case DoorstepSwap = '56';
    /** NX — Demand Surcharge */
    case DemandSurcharge = 'NX';
    /** HA — Sodium Ion PI978 */
    case SodiumIonPI978 = 'HA';
    /** XM — Low Value Regulatory Tax */
    case LowValueRegulatoryTax = 'XM';
    /** 0L — Product Kitting */
    case ProductKitting = '0L';
    /** KB — Cash On Delivery */
    case CashOnDelivery = 'KB';
    /** CA — Elevated Risk */
    case ElevatedRisk = 'CA';
    /** OO — Remote Area Delivery OO */
    case RemoteAreaDeliveryOO = 'OO';
    /** SX — No Signature Required */
    case NoSignatureRequired = 'SX';
    /** YY — Overweight Piece */
    case OverweightPiece = 'YY';
    /** GA — Smartphone Box */
    case SmartphoneBox = 'GA';
    /** UQ — Frozen Ice Plates L */
    case FrozenIcePlatesL = 'UQ';
    /** TT — Scheduled Delivery */
    case ScheduledDelivery = 'TT';
    /** XS — Excise Tax */
    case ExciseTax = 'XS';
    /** OA — Designated Domestic District */
    case DesignatedDomesticDistrict = 'OA';
    /** TF — Verified Delivery */
    case VerifiedDelivery = 'TF';
    /** 60 — Odd Schedule Delivery */
    case OddScheduleDelivery = '60';
    /** 62 — Odd Service Point */
    case OddServicePoint = '62';
    /** 4T — Very Important Cargo */
    case VeryImportantCargo = '4T';
    /** LJ — Walk In Unit 28C */
    case WalkInUnit28C = 'LJ';
    /** NY — Demand Overweight Piece */
    case DemandOverweightPiece = 'NY';
    /** NL — Demand Non Conveyable Piece */
    case DemandNonConveyablePiece = 'NL';
    /** 58 — Label Removal */
    case LabelRemoval = '58';
    /** HB — Sodium Ion PI977 */
    case SodiumIonPI977 = 'HB';
    /** X2 — Government Levy */
    case GovernmentLevy = 'X2';
    /** XX — Import Export Duties */
    case ImportExportDuties = 'XX';
    /** YZ — Loose Bulk TD */
    case LooseBulkTD = 'YZ';
    /** CH — Passive Data Logger Device */
    case PassiveDataLoggerDevice = 'CH';
    /** KA — Change Of Billing */
    case ChangeOfBilling = 'KA';
    /** OB — Remote Area Pickup */
    case RemoteAreaPickup = 'OB';
    /** PC — Shipment Consolidation */
    case ShipmentConsolidation = 'PC';
    /** YE — Multi Piece Shipment */
    case MultiPieceShipment = 'YE';
    /** 96 — Local Charge 96 */
    case LocalCharge96 = '96';
    /** 91 — Local Charge 91 */
    case LocalCharge91 = '91';
    /** IB — Extended Liability IB */
    case ExtendedLiabilityIB = 'IB';
    /** UW — Customer TCP1 */
    case CustomerTcp1 = 'UW';
    /** UG — Chilled Box M */
    case ChilledBoxM = 'UG';
    /** HJ — Live Animal */
    case LiveAnimal = 'HJ';
    /** WR — Document Translation */
    case DocumentTranslation = 'WR';
    /** 66 — Restrict Proactive SVP Redirect */
    case RestrictProactiveSvpRedirect = '66';
    /** OF — Remote Area Delivery OF */
    case RemoteAreaDeliveryOF = 'OF';
    /** WX — Returned Goods Entry */
    case ReturnedGoodsEntry = 'WX';
    /** XL — VAT On Non Revenue Item */
    case VatOnNonRevenueItem = 'XL';
    /** 4F — Overhang Item */
    case OverhangItem = '4F';
    /** 4V — Priority Small Package */
    case PrioritySmallPackage = '4V';
    /** LF — Last Mile ECS */
    case LastMileEcs = 'LF';
    /** U4 — Small Shipper Box 1525C */
    case SmallShipperBox1525C = 'U4';
    /** 1C — Reusable Shipper Box S */
    case ReusableShipperBoxS = '1C';
    /** 1I — Used Medical Device Box 32633 */
    case UsedMedicalDeviceBox32633 = '1I';
    /** 31 — Road Only Account */
    case RoadOnlyAccount = '31';
    /** WB — Non Routine Entry */
    case NonRoutineEntry = 'WB';
    /** WG — Broker Notification */
    case BrokerNotification = 'WG';
    /** WS — Post Clearance Modification */
    case PostClearanceModification = 'WS';
    /** QC — Alternative Pickup Address */
    case AlternativePickupAddress = 'QC';
    /** HT — Active Data Logger Handling */
    case ActiveDataLoggerHandling = 'HT';
    /** AD — Holiday Pickup */
    case HolidayPickup = 'AD';
    /** TB — Early Delivery */
    case EarlyDelivery = 'TB';
    /** WC — Duty Tax Processing WC */
    case DutyTaxProcessingWC = 'WC';
    /** 94 — Local Charge 94 */
    case LocalCharge94 = '94';
    /** ZE — Price Promotion */
    case PricePromotion = 'ZE';
    /** UP — Frozen Ice Plates M */
    case FrozenIcePlatesM = 'UP';
    /** HL — Limited Quantities ADR */
    case LimitedQuantitiesAdr = 'HL';
    /** HW — Lithium Metal PI970 Section II */
    case LithiumMetalPI970SectionII = 'HW';
    /** ZK — Placeholder Donations */
    case PlaceholderDonations = 'ZK';
    /** PN — Mail Service Process PN */
    case MailServiceProcessPN = 'PN';
    /** LG — Cold Storage */
    case ColdStorage = 'LG';
    /** 3P — Infectious Substance */
    case InfectiousSubstance = '3P';
    /** 4Q — Polystyrene Beads */
    case PolystyreneBeads = '4Q';
    /** TZ — Direct To Service Point */
    case DirectToServicePoint = 'TZ';
    /** DT — Import Billing */
    case ImportBilling = 'DT';
    /** LX — Hold For Collection */
    case HoldForCollection = 'LX';
    /** 0D — Warehousing */
    case Warehousing = '0D';
    /** WD — Clearance Authorization */
    case ClearanceAuthorization = 'WD';
    /** WE — Multiline Entry */
    case MultilineEntry = 'WE';
    /** WL — Bonded Transit */
    case BondedTransit = 'WL';
    /** WP — Restricted Destination WP */
    case RestrictedDestinationWP = 'WP';
    /** YB — Oversize Piece */
    case OversizePiece = 'YB';
    /** PU — Data Staging 06 */
    case DataStaging06 = 'PU';
    /** XC — Unrecoverable Origin Tax */
    case UnrecoverableOriginTax = 'XC';
    /** VH — Dry Ice Supply VH */
    case DryIceSupplyVH = 'VH';
    /** XU — Anti Dumping Duty */
    case AntiDumpingDuty = 'XU';
    /** W2 — Controlled Export */
    case ControlledExport = 'W2';
    /** HX — Magnetized Material */
    case MagnetizedMaterial = 'HX';
    /** OG — Extended Service Area OG */
    case ExtendedServiceAreaOG = 'OG';
    /** YN — Tail Lift Truck */
    case TailLiftTruck = 'YN';
    /** 3A — Valuation Charge */
    case ValuationCharge = '3A';
    /** 4N — Oxidizer */
    case Oxidizer = '4N';
    /** CS — Secure Handling */
    case SecureHandling = 'CS';
    /** U3 — Cryogenic Container L */
    case CryogenicContainerL = 'U3';
    /** V5 — Export Clearance Processing */
    case ExportClearanceProcessing = 'V5';
    /** LM — JDL Program */
    case JdlProgram = 'LM';
    /** HG — Perishable Cargo */
    case PerishableCargo = 'HG';
    /** WK — Bonded Storage */
    case BondedStorage = 'WK';
    /** FF — Fuel Surcharge */
    case FuelSurcharge = 'FF';
    /** HH — Excepted Quantities */
    case ExceptedQuantities = 'HH';
    /** OE — Extended Service Area OE */
    case ExtendedServiceAreaOE = 'OE';
    /** TW — Pre 0900 */
    case Pre0900 = 'TW';
    /** UT — Frozen Ice Sticks E */
    case FrozenIceSticksE = 'UT';
    /** YC — Non Stackable Pallet */
    case NonStackablePallet = 'YC';
    /** PT — Data Staging 03 */
    case DataStaging03 = 'PT';
    /** RA — Freight Services RA */
    case FreightServicesRA = 'RA';
    /** LB — Delivery Reroute */
    case DeliveryReroute = 'LB';
    /** JY — Courier Time Window */
    case CourierTimeWindow = 'JY';
    /** WV — Continuous Bond */
    case ContinuousBond = 'WV';
    /** ZZ — Taxable Base Revenue */
    case TaxableBaseRevenue = 'ZZ';
    /** QB — Early Pickup */
    case EarlyPickup = 'QB';
    /** HU — Not Restricted Dangerous Goods */
    case NotRestrictedDangerousGoods = 'HU';
    /** FE — Go Green Plus FE */
    case GoGreenPlusFE = 'FE';
    /** F0 — Go Green Plus F0 */
    case GoGreenPlusF0 = 'F0';
    /** HZ — Alcoholic Beverages HZ */
    case AlcoholicBeveragesHZ = 'HZ';
    /** 3W — Sporting Weapons */
    case SportingWeapons = '3W';
    /** 4D — Munitions Of War */
    case MunitionsOfWar = '4D';
    /** 4S — Explosive 14G */
    case Explosive14G = '4S';
    /** 4U — Volume */
    case Volume = '4U';
    /** LH — Fridge 28C */
    case Fridge28C = 'LH';
    /** 4X — Electric Engine Vehicle */
    case ElectricEngineVehicle = '4X';
    /** 4Y — Hybrid Engine Vehicle */
    case HybridEngineVehicle = '4Y';
    /** 1E — Reusable Shipper Box L */
    case ReusableShipperBoxL = '1E';
    /** WM — Temporary Import Export */
    case TemporaryImportExport = 'WM';
    /** YM — Loose Bulk Mother */
    case LooseBulkMother = 'YM';
    /** GE — Tablet Box */
    case TabletBox = 'GE';
    /** XF — Domestic Postal Tax */
    case DomesticPostalTax = 'XF';
    /** PS — Piece Labeling */
    case PieceLabeling = 'PS';
    /** UN — Frozen Ice Sticks L */
    case FrozenIceSticksL = 'UN';
    /** HF — Flowers */
    case Flowers = 'HF';
    /** HS — Fruits And Vegetables */
    case FruitsAndVegetables = 'HS';
    /** PZ — Label Free */
    case LabelFree = 'PZ';
    /** PM — Additional Shipment Data Merge */
    case AdditionalShipmentDataMerge = 'PM';
    /** 63 — Odd Alternate Address */
    case OddAlternateAddress = '63';
    /** 4L — Miscellaneous Dangerous Goods */
    case MiscellaneousDangerousGoods = '4L';
    /** 4P — Toxic Gas */
    case ToxicGas = '4P';
    /** YO — Non Conveyable Piece Weight */
    case NonConveyablePieceWeight = 'YO';
    /** 52 — Real Time Visibility Required */
    case RealTimeVisibilityRequired = '52';
    /** 54 — Secure Protection Same Day */
    case SecureProtectionSameDay = '54';
    /** 1H — Used Medical Device Box EP270 */
    case UsedMedicalDeviceBoxEP270 = '1H';
    /** WO — Export Declaration WO */
    case ExportDeclarationWO = 'WO';
    /** DD — Duty Tax Paid */
    case DutyTaxPaid = 'DD';
    /** KD — Printed Invoice */
    case PrintedInvoice = 'KD';
    /** UA — Thermo Packaging */
    case ThermoPackaging = 'UA';
    /** II — Shipment Insurance II */
    case ShipmentInsuranceII = 'II';
    /** OC — Remote Area Delivery OC */
    case RemoteAreaDeliveryOC = 'OC';
    /** US — Combination Dry Ice */
    case CombinationDryIce = 'US';
    /** YJ — PRM 1030 */
    case Prm1030 = 'YJ';
    /** Y1 — Piece Weight Exemption */
    case PieceWeightExemption = 'Y1';
    /** Y2 — Shipment Weight Exemption */
    case ShipmentWeightExemption = 'Y2';
    /** SB — Content Signature */
    case ContentSignature = 'SB';
    /** XK — Regulatory Charges */
    case RegulatoryCharges = 'XK';
    /** ZG — Commercial Gesture */
    case CommercialGesture = 'ZG';
    /** WN — Release To Broker */
    case ReleaseToBroker = 'WN';
    /** IG — Extended Liability IG */
    case ExtendedLiabilityIG = 'IG';
    /** 0W — Brokerage Services */
    case BrokerageServices = '0W';
    /** KR — Direct Debit */
    case DirectDebit = 'KR';
    /** LD — Intercept Routing */
    case InterceptRouting = 'LD';
    /** FD — Go Green Plus FD */
    case GoGreenPlusFD = 'FD';
    /** 3B — Dangerous Goods Defined By Host */
    case DangerousGoodsDefinedByHost = '3B';
    /** 3X — Hatching Eggs */
    case HatchingEggs = '3X';
    /** 4G — Meat */
    case Meat = '4G';
    /** 43 — Time Window Delivery 1315 */
    case TimeWindowDelivery1315 = '43';
    /** 59 — Data Logger Read Out */
    case DataLoggerReadOut = '59';
    /** H8 — ADR Special Provision */
    case AdrSpecialProvision = 'H8';
    /** FP — Go Green Plus FP */
    case GoGreenPlusFP = 'FP';
    /** DS — Duties And Taxes Unpaid */
    case DutiesAndTaxesUnpaid = 'DS';
    /** UC — Ambient Non Insulated */
    case AmbientNonInsulated = 'UC';
    /** QA — Dedicated Pickup */
    case DedicatedPickup = 'QA';
    /** SA — Delivery Signature */
    case DeliverySignature = 'SA';
    /** WA — Single Clearance */
    case SingleClearance = 'WA';
    /** DE — Receiver Paid */
    case ReceiverPaid = 'DE';
    /** CC — Security Validation */
    case SecurityValidation = 'CC';
    /** 99 — Local Charge 99 */
    case LocalCharge99 = '99';
    /** YK — PRM 1200 */
    case Prm1200 = 'YK';
    /** W5 — Clearance Processing */
    case ClearanceProcessing = 'W5';
    /** PL — Optical Character Recognition */
    case OpticalCharacterRecognition = 'PL';
    /** 64 — Odd Neighbour */
    case OddNeighbour = '64';
    /** P1 — E Mobile Online Shipment */
    case EMobileOnlineShipment = 'P1';
    /** 3D — Lithium Ion Metal B3D */
    case LithiumIonMetalB3D = '3D';
    /** 3G — Magnetized Material 3G */
    case MagnetizedMaterial3G = '3G';
    /** 3J — Flowers 3J */
    case Flowers3J = '3J';
    /** 4I — Corrosive */
    case Corrosive = '4I';
    /** NC — Demand Non Stackable Pallet */
    case DemandNonStackablePallet = 'NC';
    /** 53 — ID Check */
    case IdCheck = '53';
    /** 47 — Contract Document Return */
    case ContractDocumentReturn = '47';
    /** VJ — Export Permits And Licenses */
    case ExportPermitsAndLicenses = 'VJ';
    /** CQ — Emergency Situation CQ */
    case EmergencySituationCQ = 'CQ';
    /** U5 — Medium Shipper Box 1525C */
    case MediumShipperBox1525C = 'U5';
    /** U8 — Ambient Shipper Box M */
    case AmbientShipperBoxM = 'U8';
    /** 1B — Reusable Shipper Box XS */
    case ReusableShipperBoxXS = '1B';
    /** 0B — Mailroom Management */
    case MailroomManagement = '0B';
    /** 0H — Terminal Handling */
    case TerminalHandling = '0H';
    /** WQ — Preferential Origin */
    case PreferentialOrigin = 'WQ';
    /** PY — Monthly Pickup Service */
    case MonthlyPickupService = 'PY';
    /** HI — Radioactive Material */
    case RadioactiveMaterial = 'HI';
    /** AA — Saturday Delivery AA */
    case SaturdayDeliveryAA = 'AA';
    /** TV — Direct To Service Point TV */
    case DirectToServicePointTV = 'TV';
    /** XD — Quarantine Inspection */
    case QuarantineInspection = 'XD';
    /** HN — ADR Load Exemption */
    case AdrLoadExemption = 'HN';
    /** YI — PRM 0900 */
    case Prm0900 = 'YI';
    /** GK — Bulk Packaging */
    case BulkPackaging = 'GK';
    /** IE — Shipment Insurance IE */
    case ShipmentInsuranceIE = 'IE';
    /** 68 — Signature Required 68 */
    case SignatureRequired68 = '68';
    /** 69 — Restrict Leave With Neighbour */
    case RestrictLeaveWithNeighbour = '69';
    /** KC — Contract Billing */
    case ContractBilling = 'KC';
    /** PH — Return To Seller */
    case ReturnToSeller = 'PH';
    /** LU — Sanctions Routing */
    case SanctionsRouting = 'LU';
    /** SD — Adult Signature */
    case AdultSignature = 'SD';
    /** IW — Under Bond Guarantee */
    case UnderBondGuarantee = 'IW';
    /** 61 — Odd Signature Release */
    case OddSignatureRelease = '61';
    /** LS — Time Definite Plus */
    case TimeDefinitePlus = 'LS';
    /** RD — Toll Surcharge */
    case TollSurcharge = 'RD';
    /** 3H — Mail */
    case Mail = '3H';
    /** 4K — Flammable Solid */
    case FlammableSolid = '4K';
    /** 1D — Reusable Shipper Box M */
    case ReusableShipperBoxM = '1D';
    /** H1 — Active Data Logger Device */
    case ActiveDataLoggerDevice = 'H1';
    /** 32 — Third Country Domestic */
    case ThirdCountryDomestic = '32';
    /** 3V — Explosive 14S */
    case Explosive14S = '3V';
}
