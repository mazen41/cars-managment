<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CarInspectionType;
use App\Models\CarInspectionSection;
use App\Models\CarInspectionField;

class CarInspectionSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Seller Form Inspection Type
        $sellerForm = CarInspectionType::create([
            "name" => "Seller Form",
            "slug" => "seller-form",
            "description" =>
                "Basic information form for sellers listing their cars",
            "is_active" => true,
            "sort_order" => 1,
        ]);

        // Create Buyer Basic Test Inspection Type
        $buyerBasic = CarInspectionType::create([
            "name" => "Buyer Basic Test",
            "slug" => "buyer-basic-test",
            "description" => "Basic inspection checklist for potential buyers",
            "is_active" => true,
            "sort_order" => 2,
        ]);

        // Create Buyer Advanced Test Inspection Type
        $buyerAdvanced = CarInspectionType::create([
            "name" => "Buyer Advanced Test",
            "slug" => "buyer-advanced-test",
            "description" =>
                "Comprehensive professional inspection for serious buyers",
            "is_active" => true,
            "sort_order" => 3,
        ]);

        // Create sections and fields for Seller Form
        $this->createSellerFormSections($sellerForm);

        // Create sections and fields for Buyer Basic Test
        $this->createBuyerBasicSections($buyerBasic);

        // Create sections and fields for Buyer Advanced Test
        $this->createBuyerAdvancedSections($buyerAdvanced);
    }

    private function createSellerFormSections($inspectionType)
    {
        // Vehicle Information Section
        $vehicleInfo = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Vehicle Information",
            "slug" => "vehicle-information",
            "description" => "Basic vehicle details and specifications",
            "is_active" => true,
            "sort_order" => 1,
        ]);

        $this->createVehicleInfoFields($vehicleInfo);

        // Condition Assessment Section
        $condition = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Condition Assessment",
            "slug" => "condition-assessment",
            "description" => "Overall condition and maintenance history",
            "is_active" => true,
            "sort_order" => 2,
        ]);

        $this->createConditionFields($condition);

        // Documentation Section
        $documentation = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Documentation",
            "slug" => "documentation",
            "description" => "Required documents and legal information",
            "is_active" => true,
            "sort_order" => 3,
        ]);

        $this->createDocumentationFields($documentation);
    }

    private function createBuyerBasicSections($inspectionType)
    {
        // Exterior Inspection
        $exterior = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Exterior Inspection",
            "slug" => "exterior-inspection",
            "description" => "Visual inspection of vehicle exterior",
            "is_active" => true,
            "sort_order" => 1,
        ]);

        $this->createExteriorFields($exterior);

        // Interior Inspection
        $interior = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Interior Inspection",
            "slug" => "interior-inspection",
            "description" => "Assessment of interior condition and features",
            "is_active" => true,
            "sort_order" => 2,
        ]);

        $this->createInteriorFields($interior);

        // Engine Bay
        $engineBay = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Engine Bay",
            "slug" => "engine-bay",
            "description" => "Basic engine compartment inspection",
            "is_active" => true,
            "sort_order" => 3,
        ]);

        $this->createEngineBasicFields($engineBay);

        // Test Drive
        $testDrive = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Test Drive",
            "slug" => "test-drive",
            "description" => "Driving performance assessment",
            "is_active" => true,
            "sort_order" => 4,
        ]);

        $this->createTestDriveFields($testDrive);
    }

    private function createBuyerAdvancedSections($inspectionType)
    {
        // All basic sections plus advanced ones
        $this->createBuyerBasicSections($inspectionType);

        // Mechanical Systems
        $mechanical = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Mechanical Systems",
            "slug" => "mechanical-systems",
            "description" => "Detailed mechanical component inspection",
            "is_active" => true,
            "sort_order" => 5,
        ]);

        $this->createMechanicalFields($mechanical);

        // Electrical Systems
        $electrical = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Electrical Systems",
            "slug" => "electrical-systems",
            "description" => "Electrical components and systems check",
            "is_active" => true,
            "sort_order" => 6,
        ]);

        $this->createElectricalFields($electrical);

        // Safety Features
        $safety = CarInspectionSection::create([
            "inspection_type_id" => $inspectionType->id,
            "name" => "Safety Features",
            "slug" => "safety-features",
            "description" => "Safety systems and equipment inspection",
            "is_active" => true,
            "sort_order" => 7,
        ]);

        $this->createSafetyFields($safety);
    }

    private function createVehicleInfoFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Vehicle Identification Number (VIN)",
            "slug" => "vin",
            "description" => "Enter the 17-character VIN",
            "field_type" => "text",
            "is_required" => true,
            "sort_order" => 1,
            "validation_rules" => ["min:17", "max:17"],
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Odometer Reading",
            "slug" => "odometer",
            "description" => "Current mileage in kilometers",
            "field_type" => "number",
            "is_required" => true,
            "sort_order" => 2,
            "validation_rules" => ["min:0"],
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Number of Previous Owners",
            "slug" => "previous-owners",
            "field_type" => "select",
            "field_options" => [
                "options" => ["1", "2", "3", "4", "5+"],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Accident History",
            "slug" => "accident-history",
            "description" => "Has the vehicle been in any accidents?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 4,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Accident Details",
            "slug" => "accident-details",
            "description" => "If yes, please provide details",
            "field_type" => "textarea",
            "is_required" => false,
            "sort_order" => 5,
        ]);
    }

    private function createConditionFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Overall Condition",
            "slug" => "overall-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => ["Excellent", "Good", "Fair", "Poor"],
            ],
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Regular Maintenance",
            "slug" => "regular-maintenance",
            "description" => "Has the vehicle been regularly maintained?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Service Records Available",
            "slug" => "service-records",
            "description" => "Are service records available?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Known Issues",
            "slug" => "known-issues",
            "description" => "List any known mechanical or cosmetic issues",
            "field_type" => "textarea",
            "is_required" => false,
            "sort_order" => 4,
        ]);
    }

    private function createDocumentationFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Vehicle Registration",
            "slug" => "vehicle-registration",
            "description" => "Is vehicle registration current and available?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Safety Certificate",
            "slug" => "safety-certificate",
            "description" => "Is safety certificate valid?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Emission Test",
            "slug" => "emission-test",
            "description" => "Is emission test up to date?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Warranty Status",
            "slug" => "warranty-status",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "None",
                    "Manufacturer Warranty",
                    "Extended Warranty",
                    "Third Party Warranty",
                ],
            ],
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }

    private function createExteriorFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Paint Condition",
            "slug" => "paint-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Excellent",
                    "Good",
                    "Fair",
                    "Poor",
                    "Needs Attention",
                ],
            ],
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Body Damage",
            "slug" => "body-damage",
            "field_type" => "checkbox",
            "field_options" => [
                "options" => [
                    "Scratches",
                    "Dents",
                    "Rust",
                    "Collision Damage",
                    "None",
                ],
            ],
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Tire Condition",
            "slug" => "tire-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "New",
                    "Good",
                    "Fair",
                    "Worn",
                    "Needs Replacement",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Windshield Condition",
            "slug" => "windshield-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Perfect",
                    "Minor Chips",
                    "Cracked",
                    "Needs Replacement",
                ],
            ],
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }

    private function createInteriorFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Seat Condition",
            "slug" => "seat-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => ["Excellent", "Good", "Fair", "Worn", "Damaged"],
            ],
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Dashboard Condition",
            "slug" => "dashboard-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => ["Perfect", "Good", "Faded", "Cracked", "Damaged"],
            ],
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Electronics Working",
            "slug" => "electronics-working",
            "field_type" => "checkbox",
            "field_options" => [
                "options" => [
                    "Radio",
                    "A/C",
                    "Heater",
                    "Power Windows",
                    "Power Locks",
                    "GPS",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Interior Cleanliness",
            "slug" => "interior-cleanliness",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Very Clean",
                    "Clean",
                    "Fair",
                    "Dirty",
                    "Very Dirty",
                ],
            ],
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }

    private function createEngineBasicFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Engine Starts Easily",
            "slug" => "engine-starts",
            "description" => "Does the engine start without issues?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Engine Idle Quality",
            "slug" => "engine-idle",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Smooth",
                    "Slightly Rough",
                    "Rough",
                    "Very Rough",
                ],
            ],
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Fluid Levels",
            "slug" => "fluid-levels",
            "field_type" => "checkbox",
            "field_options" => [
                "options" => [
                    "Oil OK",
                    "Coolant OK",
                    "Brake Fluid OK",
                    "Power Steering OK",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Visible Leaks",
            "slug" => "visible-leaks",
            "description" => "Are there any visible fluid leaks?",
            "field_type" => "boolean",
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }

    private function createTestDriveFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Transmission Performance",
            "slug" => "transmission-performance",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Excellent",
                    "Good",
                    "Fair",
                    "Poor",
                    "Problematic",
                ],
            ],
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Braking Performance",
            "slug" => "braking-performance",
            "field_type" => "select",
            "field_options" => [
                "options" => ["Excellent", "Good", "Fair", "Poor", "Unsafe"],
            ],
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Steering Response",
            "slug" => "steering-response",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Responsive",
                    "Good",
                    "Fair",
                    "Loose",
                    "Problematic",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Unusual Noises",
            "slug" => "unusual-noises",
            "description" => "Any unusual noises during test drive?",
            "field_type" => "textarea",
            "is_required" => false,
            "sort_order" => 4,
        ]);
    }

    private function createMechanicalFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Engine Compression Test",
            "slug" => "compression-test",
            "description" => "Compression test results (PSI)",
            "field_type" => "text",
            "is_required" => false,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Suspension Components",
            "slug" => "suspension-components",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Excellent",
                    "Good",
                    "Worn",
                    "Needs Attention",
                    "Needs Replacement",
                ],
            ],
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Exhaust System",
            "slug" => "exhaust-system",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Perfect",
                    "Good",
                    "Minor Issues",
                    "Needs Repair",
                    "Needs Replacement",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Timing Belt/Chain",
            "slug" => "timing-belt",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Recently Replaced",
                    "Good Condition",
                    "Needs Attention",
                    "Needs Replacement",
                    "Unknown",
                ],
            ],
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }

    private function createElectricalFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Battery Condition",
            "slug" => "battery-condition",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "New",
                    "Good",
                    "Fair",
                    "Weak",
                    "Needs Replacement",
                ],
            ],
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Alternator Output",
            "slug" => "alternator-output",
            "description" => "Voltage output at idle",
            "field_type" => "number",
            "is_required" => false,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Warning Lights",
            "slug" => "warning-lights",
            "field_type" => "checkbox",
            "field_options" => [
                "options" => [
                    "Check Engine",
                    "ABS",
                    "Airbag",
                    "Oil Pressure",
                    "Battery",
                    "None",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "All Lights Working",
            "slug" => "lights-working",
            "field_type" => "checkbox",
            "field_options" => [
                "options" => [
                    "Headlights",
                    "Tail Lights",
                    "Brake Lights",
                    "Turn Signals",
                    "Hazards",
                    "Interior Lights",
                ],
            ],
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }

    private function createSafetyFields($section)
    {
        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Airbag System",
            "slug" => "airbag-system",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "All Working",
                    "Some Issues",
                    "Warning Light On",
                    "Not Working",
                    "Unknown",
                ],
            ],
            "is_required" => true,
            "sort_order" => 1,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "ABS System",
            "slug" => "abs-system",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "Working Properly",
                    "Warning Light",
                    "Not Working",
                    "Not Equipped",
                ],
            ],
            "is_required" => true,
            "sort_order" => 2,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Seatbelts",
            "slug" => "seatbelts",
            "field_type" => "select",
            "field_options" => [
                "options" => [
                    "All Working",
                    "Some Issues",
                    "Damaged",
                    "Missing",
                ],
            ],
            "is_required" => true,
            "sort_order" => 3,
        ]);

        CarInspectionField::create([
            "section_id" => $section->id,
            "name" => "Emergency Equipment",
            "slug" => "emergency-equipment",
            "field_type" => "checkbox",
            "field_options" => [
                "options" => [
                    "Fire Extinguisher",
                    "First Aid Kit",
                    "Emergency Triangle",
                    "Spare Tire",
                    "Jack",
                    "None",
                ],
            ],
            "is_required" => true,
            "sort_order" => 4,
        ]);
    }
}
