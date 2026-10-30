/* ==========================================================================
   RELIANCE GRANITO - EXPORT DOCUMENTATION MANAGEMENT SYSTEM (EDMS)
   Comprehensive Data Model & Seed Store based on Reliance Granito Export Workflow
   Source Reference: reliance_granito_export_workflow.pdf & Consignment 1469
   ========================================================================== */

const RelianceEDMSSeed = {
  // Single Authorized Login Profile
  currentUser: {
    id: "USR-001",
    name: "Rajesh B. Patel",
    email: "admin@reliancegranito.com",
    roleTitle: "Export Documentation Manager",
    avatar: "RG"
  },

  // Exporter & Master Parties
  parties: [
    {
      id: "EXP-001",
      partyType: "Exporter",
      name: "Reliance Granito",
      address: "Sector 23, 23-A-6, Chopasni Housing Board, Opposite Ashok Udhyan Gate No. 2, Jodhpur, Rajasthan - 342008, India",
      factoryAddress: "National Highway 8-A, Old Ghuntu Road, Gujarat - 363642, India",
      iec: "ABEFR3842G",
      iecBranch: "0",
      gstin: "08ABEFR3842G1Z5",
      taxCode: "4646655196",
      adCode: "6390013",
      forexBank: "ICICI Bank Limited, Jodhpur Branch (A/C: ICIC0006827)",
      contact: "Rajesh B. Patel (+91 2822 240890 / reliancegranito@gmail.com)",
      web: "www.reliancegranito.com",
      status: "Active"
    },
    {
      id: "BUY-1469",
      partyType: "Buyer / Importer",
      name: "CJ Stone Company Limited",
      address: "No 10, Nghe Tinh, Da Nang, Vietnam",
      editedAddress: "Dong Hai Ward, Hai Phong City, Vietnam",
      country: "Vietnam, Democratic Rep. Of",
      port: "Haiphong Port (VNHPH)",
      taxCode: "040124712551",
      contact: "Nguyen Van Thang (imp.cjstone@gmail.com)",
      incoterms: "CIF Hai Phong Port",
      paymentTerms: "100% Against Bill of Lading (CAD / TT)",
      status: "Active"
    },
    {
      id: "NOT-1469-A",
      partyType: "Notify Party 1",
      name: "Hung Thuc Trading Investment and Import Export Company Limited",
      address: "No 25/14 Nguyen Binh Khiem, Hai Phong City, Vietnam",
      country: "Vietnam",
      contact: "Tran Duc Hung (+84 225 382 9912)",
      status: "Active"
    },
    {
      id: "NOT-1469-B",
      partyType: "Notify Party 2",
      name: "Tran Gia Logistics Limited Company",
      address: "Room 402, TD Business Center, Le Hong Phong Street, Ngo Quyen, Hai Phong, Vietnam",
      country: "Vietnam",
      contact: "Doan Quoc Bao (+84 904 123 789)",
      status: "Active"
    },
    {
      id: "NOT-1469-C",
      partyType: "Notify Party 3",
      name: "SSG International PTE Ltd",
      address: "10 Anson Road #26-04 International Plaza, Singapore 079903",
      country: "Singapore",
      contact: "K. S. Tan (+65 6224 8819)",
      status: "Active"
    },
    {
      id: "CB-001",
      partyType: "Customs Broker",
      name: "Blue Water Logistics Limited",
      cbCode: "AALCB0113DCH001",
      address: "Customs House Area, 60 Rajaji Salai, Chennai - 600001, Tamil Nadu, India",
      pan: "AALCB0113D",
      phone: "+91 44 2522 9011",
      status: "Active"
    },
    {
      id: "BUY-002",
      partyType: "Buyer / Importer",
      name: "Baumarkt Import GmbH",
      address: "Industriestrasse 42, 20457 Hamburg, Germany",
      country: "Germany",
      port: "Hamburg Port (DEHAM)",
      taxCode: "DE812399104",
      contact: "Klaus Von Weber (klaus.weber@baumarkt-import.de)",
      incoterms: "FOB Mundra Port",
      paymentTerms: "100% Irrevocable LC at Sight",
      status: "Active"
    },
    {
      id: "BUY-003",
      partyType: "Buyer / Importer",
      name: "Al-Futtaim Building Materials LLC",
      address: "Al-Futtaim Logistics Park, Jebel Ali Industrial Area 1, Dubai, UAE",
      country: "United Arab Emirates",
      port: "Jebel Ali Port (AEJEA)",
      taxCode: "AE100294100",
      contact: "Tariq Al-Mansoor (t.mansoor@alfuttaim-tiles.ae)",
      incoterms: "CIF Jebel Ali",
      paymentTerms: "30% Advance TT, 70% against BL copy",
      status: "Active"
    }
  ],

  // Masters: Goods & Products
  products: [
    {
      id: "PRD-GRAN-01",
      name: "Polished Granite Slabs (Rough / Dressed / Cut)",
      hsn: "68022310",
      uqc: "SQM",
      description: "Indian Natural Polished Granite Slabs for flooring and wall cladding, thickness 20mm mirror flamed",
      unitPriceUsd: 31.666,
      standardGrossWtPerSqm: 51.08,
      packaging: "Wooden Bundles (ISPM-15 Heat Treated)"
    },
    {
      id: "PRD-TILE-6012",
      name: "Armani Gold Carving GVT (600x1200mm)",
      hsn: "69072100",
      uqc: "SQM",
      description: "Glazed Vitrified Porcelain Floor Tiles with water absorption <0.05%, rectificated edges",
      unitPriceUsd: 4.85,
      standardGrossWtPerSqm: 20.83,
      packaging: "Corrugated Cartons on Euro Pallets"
    },
    {
      id: "PRD-SLAB-8016",
      name: "Statuario Extra White Porcelain Grand Slab (800x1600mm)",
      hsn: "69072100",
      uqc: "SQM",
      description: "Bookmatch High Gloss Mirror Polished Porcelain Surface, 9mm thickness",
      unitPriceUsd: 7.20,
      standardGrossWtPerSqm: 20.31,
      packaging: "Iron A-Frame & Wooden Crates"
    }
  ],

  // Masters: HSN Codes
  hsnCodes: [
    { code: "68022310", description: "Granite slabs polished, decorated or otherwise worked", gstRate: 0, rodtpRate: "1.5%", dbkRate: "1.2%" },
    { code: "69072100", description: "Ceramic flags and paving, hearth or wall tiles, water absorption <= 0.5%", gstRate: 0, rodtpRate: "1.4%", dbkRate: "1.0%" },
    { code: "69072200", description: "Ceramic tiles, water absorption > 0.5% but <= 10%", gstRate: 0, rodtpRate: "1.2%", dbkRate: "0.8%" }
  ],

  // Masters: Ports & Routes
  ports: [
    { code: "INMAA1", name: "Chennai Sea Port (Ex Madras)", country: "India", type: "POL" },
    { code: "INMUN1", name: "Mundra Port (Adani MICT / AMCT)", country: "India", type: "POL" },
    { code: "INPAV1", name: "Pipavav Sea Port", country: "India", type: "POL" },
    { code: "VNHPH", name: "Hai Phong Port", country: "Vietnam", type: "POD" },
    { code: "AEJEA", name: "Jebel Ali Port, Dubai", country: "United Arab Emirates", type: "POD" },
    { code: "DEHAM", name: "Hamburg Port (Waltershof / CTA)", country: "Germany", type: "POD" },
    { code: "USHOU", name: "Houston Port, Texas", country: "United States", type: "POD" }
  ],

  // Masters: Carriers & Shipping Lines
  carriers: [
    { id: "CAR-01", name: "Mediterranean Shipping Company (MSC)", scac: "MSCU", mode: "Ocean Sea Freight" },
    { id: "CAR-02", name: "Maersk Line / A.P. Moller", scac: "MAEU", mode: "Ocean Sea Freight" },
    { id: "CAR-03", name: "CMA CGM Group", scac: "CMDU", mode: "Ocean Sea Freight" },
    { id: "CAR-04", name: "Hapag-Lloyd AG", scac: "HLCU", mode: "Ocean Sea Freight" }
  ],

  // Masters: Document Templates & HIDE Field Visibility Map
  templates: [
    {
      id: "TPL-INV-01",
      name: "Reliance Granito Standard Commercial Invoice",
      docType: "Commercial Invoice",
      version: "v2.4",
      hideSupported: false
    },
    {
      id: "TPL-SB-06P",
      name: "Indian Customs EDI Shipping Bill (6-Page ICEGATE Form)",
      docType: "Shipping Bill",
      version: "v4.1",
      hideSupported: true,
      hideRegions: ["Customs Valuation FOB INR", "Duty Drawback Amount", "CB Pan / Broker Details", "Buyer Tax Code"]
    },
    {
      id: "TPL-BL-01",
      name: "Multimodal Ocean Bill of Lading (FIATA / Carrier Spec)",
      docType: "Bill of Lading",
      version: "v3.0",
      hideSupported: true,
      hideRegions: ["Consignee Details", "Notify Party Contact", "Freight Prepaid Amount", "Carrier Booking Reference"]
    }
  ],

  // =========================================================================
  // CORE SHIPMENTS REPOSITORY
  // =========================================================================
  shipments: [
    {
      shipmentNumber: "SHP-1469",
      exporterPartyId: "EXP-001",
      buyerPartyId: "BUY-1469",
      status: "Discrepancy Flagged", // Draft, In-Review, Discrepancy Flagged, Approved, Finalized
      reviewStatus: "Awaiting Cross-Validation Resolution",
      createdAt: "2026-02-14",
      createdBy: "Pooja Sharma (Documentation Officer)",
      approvedBy: null,
      approvedAt: null,
      finalizedAt: null,
      polCode: "INMAA1",
      polName: "Chennai Sea Port, India",
      podCode: "VNHPH",
      podName: "Hai Phong Port, Vietnam",
      incoterms: "CIF Hai Phong Port",
      mode: "SEA",

      // Goods & Quantities Summary
      totalQuantitySqm: 518.69,
      totalPackages: 226,
      packageType: "Wooden Bundles / Pallets",
      grossWeightKg: 26500,
      netWeightKg: 26000,
      currency: "USD",
      declaredValueFob: 15425.27,
      freightCharges: 900.00,
      insuranceCharges: 100.00,
      totalInvoiceValue: 16425.27,

      // Associated Core Documents (Document IDs)
      commercialInvoiceId: "INV-1469",
      shippingBillId: "SB-9716398",
      billOfLadingId: "BL-MEDU-146908",

      // Containers
      containers: [
        {
          containerNumber: "TCLU7789571",
          type: "20ft Standard Heavy Duty Marine Box",
          sealNumber: "14697",
          packageCount: 226,
          grossWeightKg: 26500,
          netWeightKg: 26000,
          tareWeightKg: 2250,
          status: "Customs Stuffed & Gate-In Passed"
        }
      ],

      // Source PDF Attachments
      attachments: [
        { name: "1469 INVOICE ORIGINAL.pdf", type: "Original Source Invoice", size: "456 KB", path: "docs/1469 INVOICE ORIGINAL.pdf", verified: true },
        { name: "1469 INV EDITED.pdf", type: "Edited Invoice Revision", size: "438 KB", path: "docs/1469 INV EDITED.pdf", verified: true },
        { name: "1469 SB ORIGINAL.PDF", type: "Official Customs Shipping Bill", size: "514 KB", path: "docs/1469 SB ORIGINAL.PDF", verified: true },
        { name: "1469 SB HIDE.pdf", type: "Shipping Bill HIDE Reference", size: "4.2 MB", path: "docs/1469 SB HIDE.pdf", verified: true },
        { name: "1469 BL.pdf", type: "Ocean Bill of Lading", size: "935 KB", path: "docs/1469 BL.pdf", verified: true },
        { name: "BL HIDE.pdf", type: "Bill of Lading HIDE Mask Reference", size: "830 KB", path: "docs/BL HIDE.pdf", verified: true }
      ]
    },
    {
      shipmentNumber: "SHP-1470",
      exporterPartyId: "EXP-001",
      buyerPartyId: "BUY-002",
      status: "Approved",
      reviewStatus: "Finalized - Ready for Bank Presentation",
      createdAt: "2026-02-18",
      createdBy: "Pooja Sharma",
      approvedBy: "Vikramaditya Rathore (Customs Reviewer)",
      approvedAt: "2026-02-19 14:30",
      finalizedAt: "2026-02-19 15:00",
      polCode: "INMUN1",
      polName: "Mundra Port, Gujarat, India",
      podCode: "DEHAM",
      podName: "Hamburg Port, Germany",
      incoterms: "FOB Mundra Port",
      mode: "SEA",
      totalQuantitySqm: 8640.0,
      totalPackages: 180,
      packageType: "Pallets",
      grossWeightKg: 162000,
      netWeightKg: 158400,
      currency: "USD",
      declaredValueFob: 41904.0,
      freightCharges: 0,
      insuranceCharges: 0,
      totalInvoiceValue: 41904.0,
      commercialInvoiceId: "INV-1470",
      shippingBillId: "SB-9810234",
      billOfLadingId: "BL-HLCU-889102",
      containers: [
        { containerNumber: "MSKU9428172", type: "20ft Box", sealNumber: "IN-RG-8491", packageCount: 30, grossWeightKg: 27000 }
      ],
      attachments: []
    },
    {
      shipmentNumber: "SHP-1471",
      exporterPartyId: "EXP-001",
      buyerPartyId: "BUY-003",
      status: "In-Review",
      reviewStatus: "ICEGATE LEO Verification Pending",
      createdAt: "2026-02-20",
      createdBy: "Rajesh B. Patel",
      approvedBy: null,
      approvedAt: null,
      finalizedAt: null,
      polCode: "INMUN1",
      polName: "Mundra Port, Gujarat, India",
      podCode: "AEJEA",
      podName: "Jebel Ali Port, UAE",
      incoterms: "CIF Jebel Ali",
      mode: "SEA",
      totalQuantitySqm: 5760.0,
      totalPackages: 100,
      packageType: "Pallets",
      grossWeightKg: 108500,
      netWeightKg: 106000,
      currency: "USD",
      declaredValueFob: 31104.0,
      freightCharges: 3200,
      insuranceCharges: 190,
      totalInvoiceValue: 34494.0,
      commercialInvoiceId: "INV-1471",
      shippingBillId: "SB-9892019",
      billOfLadingId: "BL-MSKU-202604",
      containers: [
        { containerNumber: "MEDU7821940", type: "20ft Box", sealNumber: "MSC-7741", packageCount: 25, grossWeightKg: 27100 }
      ],
      attachments: []
    }
  ],

  // =========================================================================
  // COMMERCIAL INVOICES REPOSITORY (ORIGINAL vs EDITED vs PREVIEW)
  // =========================================================================
  commercialInvoices: [
    {
      id: "INV-1469",
      invoiceNumber: "1469/25-26",
      invoiceDate: "2026-02-14",
      shipmentNumber: "SHP-1469",
      version: "v1.1 (Edited)",
      presentationState: "Edited", // Original, Edited, Preview
      currency: "USD",
      exchangeRateInr: 89.47,
      incoterms: "CIF Hai Phong Port, Vietnam",
      lutNumber: "LUT-AD080426009028",
      terms: "Supply meant for export under Bond or Letter of Undertaking without payment of IGST",

      exporterName: "Reliance Granito",
      exporterAddress: "Sector 23, 23-A-6, Chopasni Housing Board, Opposite Ashok Udhyan Gate No. 2, Jodhpur, Rajasthan - 342008, India",
      exporterIec: "ABEFR3842G",
      exporterGstin: "08ABEFR3842G1Z5",

      // Buyer in Original vs Edited:
      buyerNameOriginal: "CJ Stone Company Limited",
      buyerAddressOriginal: "No 10, Nghe Tinh, Da Nang, Vietnam",
      buyerNameEdited: "CJ Stone Company Limited",
      buyerAddressEdited: "Dong Hai Ward, Hai Phong City, Vietnam",
      buyerTaxCode: "040124712551",

      notifyParty1: "Hung Thuc Trading Investment and Import Export Company Limited",
      notifyParty2: "Tran Gia Logistics Limited Company",
      notifyParty3: "SSG International PTE Ltd",

      pol: "Chennai Sea Port, India (INMAA1)",
      pod: "Hai Phong Port, Vietnam (VNHPH)",
      finalDestination: "Hai Phong Port, Vietnam",
      carrierVessel: "MSC Ravel V.1469W / Blue Water Logistics",

      // Line Items
      items: [
        {
          itemNo: 1,
          description: "Indian Polished Granite Slabs (Black Galaxy / Tan Brown)",
          hsn: "68022310",
          quantity: 518.69,
          uqc: "SQM",
          rate: 29.738,
          lineAmountUsd: 15425.27,
          packages: 226,
          grossWeightKg: 26500,
          netWeightKg: 26000
        }
      ],

      subtotalFobUsd: 15425.27,
      freightChargesUsd: 900.00,
      insuranceChargesUsd: 100.00,
      invoiceTotalUsd: 16425.27,
      amountInWordsUsd: "US Dollars Sixteen Thousand Four Hundred Twenty Five and Twenty Seven Cents Only",

      // Discrepancy flags / overrides recorded
      manualOverrides: [
        { field: "Consignee Address", original: "No 10, Nghe Tinh, Da Nang", edited: "Dong Hai Ward, Hai Phong City", confirmedBy: "Rajesh B. Patel", timestamp: "2026-02-15 11:20" }
      ],
      isFinalized: false
    }
  ],

  // =========================================================================
  // SHIPPING BILLS REPOSITORY (6-PAGE EDI SCHEMA)
  // =========================================================================
  shippingBills: [
    {
      id: "SB-9716398",
      sbNumber: "9716398",
      sbDate: "2026-02-16",
      shipmentNumber: "SHP-1469",
      customsHouse: "Customs House, 60 Rajaji Salai, Chennai - 600001",
      portCode: "INMAA1",
      portName: "Chennai Sea (Ex Madras)",
      iec: "ABEFR3842G",
      branchCode: "0",
      cbCode: "AALCB0113DCH001",
      cbName: "Blue Water Logistics Limited",
      gstin: "08ABEFR3842G1Z5",
      stateOfOrigin: "Rajasthan",
      countryOfDestination: "Vietnam, Democratic Rep. Of",
      portOfDestination: "VNHPH (Haiphong)",
      modeOfTransport: "SEA",
      status: "LEO Granted (Let Export Order)",
      leoDate: "2026-02-17 16:02",
      sourcePdfPath: "docs/1469 SB ORIGINAL.PDF",
      sourcePdfVerified: true,

      // 6-Page Mapping Breakdown:
      page1_summary: {
        scheme: "LUT (Letter of Undertaking) / Duty Drawback Claimed",
        adCode: "6390013",
        forexAccount: "ICIC0006827",
        dbkAccount: "ICIC0006827",
        totalPackages: 226,
        grossWeightKg: 26500,
        netWeightKg: 26000,
        invoiceNumberRef: "1469/25-26",
        invoiceAmountUsd: 16425.27,
        fobValueInr: 1469656.66
      },
      page2_items: [
        {
          itemNo: 1,
          hsn: "68022310",
          description: "Polished Granite Slabs",
          quantity: 518.69,
          uqc: "SQM",
          rateFobUsd: 29.738,
          fobInr: 1379965.00,
          pmvInr: 1550000.00,
          drawbackClaimInr: 14697.00,
          igstStatus: "Export Under LUT Without Payment Of Tax",
          schemeCode: "19 (Drawback & RoDTEP)"
        }
      ],
      page3_valuation: {
        currency: "USD",
        exchangeRate: 89.475,
        totalInvoiceUsd: 16425.27,
        freightUsd: 900.00,
        insuranceUsd: 100.00,
        fobDeclaredUsd: 15425.27,
        fobRealizedInr: 1379965.00
      },
      page4_dutiesAndCess: {
        dutyDrawbackClaimedInr: 14697.00,
        rodtepIncentiveInr: 20699.00,
        igstValue: 0.00,
        cessAmount: 0.00,
        exportDuty: 0.00
      },
      page5_equipmentContainers: [
        {
          containerNumber: "TCLU7789571",
          sealNumber: "14697",
          containerType: "20' Box",
          packages: 226,
          weightKg: 26500,
          sealType: "Electronic RFID / Customs Bottle Seal"
        }
      ],
      page6_customsProcess: {
        submissionDate: "2026-02-16 16:02",
        assessmentStatus: "Self Assessed & Passed",
        examinationStatus: "Examined & X-Ray Scanned at Chennai CFS",
        leoNumber: "LEO-CH-2026-9716398",
        leoTimestamp: "2026-02-17 17:21:31 IST",
        officerDigitalSignature: "DS CENTRAL BOARD OF INDIRECT TAXES AND CUSTOMS 10"
      }
    }
  ],

  // =========================================================================
  // BILL OF LADING (BL) REPOSITORY
  // =========================================================================
  billsOfLading: [
    {
      id: "BL-MEDU-146908",
      blNumber: "MEDU-146908",
      blDate: "2026-02-18",
      shipmentNumber: "SHP-1469",
      carrierName: "Mediterranean Shipping Company (MSC)",
      carrierAgent: "Blue Water Logistics Limited",
      vesselVoyage: "MSC Ravel V.1469W",
      bookingRef: "MSC-CHE-910488",
      pol: "Chennai Sea Port, India (INMAA1)",
      pod: "Hai Phong Port, Vietnam (VNHPH)",
      placeOfDelivery: "Hai Phong Port, Vietnam",
      freightTerms: "Freight Prepaid (CIF terms)",

      shipper: "Reliance Granito, Sector 23, Jodhpur, Rajasthan, India",
      consignee: "CJ Stone Company Limited, Dong Hai Ward, Hai Phong City, Vietnam",
      notifyParty: "Hung Thuc Trading Investment / Tran Gia Logistics, Hai Phong",

      containerNumber: "TCLU7789571",
      sealNumber: "14697",
      packageCount: 226,
      packageDesc: "Wooden Bundles Containing Polished Granite Slabs",
      grossWeightKg: 26500,
      netWeightKg: 26000,
      measurementCbm: 28.5,

      // HIDE mode visibility map configuration (as highlighted in BL HIDE.pdf)
      hideRegions: [
        { fieldKey: "consignee", label: "Consignee Name & Address", maskedInHide: true },
        { fieldKey: "freightCharges", label: "Freight Prepaid Amount", maskedInHide: true },
        { fieldKey: "notifyPartyContact", label: "Notify Party Phone/Direct Email", maskedInHide: true }
      ],
      isFinalized: false
    }
  ],

  // =========================================================================
  // CROSS-DOCUMENT VALIDATION DISCREPANCY MATRIX
  // =========================================================================
  crossValidationResults: [
    {
      shipmentNumber: "SHP-1469",
      fieldKey: "buyerAddress",
      fieldName: "Buyer / Consignee Address",
      invoiceValue: "Dong Hai Ward, Hai Phong City (Edited v1.1)",
      shippingBillValue: "Vietnam, Democratic Rep. Of (Country Level)",
      blValue: "Dong Hai Ward, Hai Phong City, Vietnam",
      status: "MISMATCH", // MATCH, MISMATCH, WARNING
      reviewNotes: "Original Invoice had Da Nang address. Buyer requested discharge at Hai Phong; edited invoice was submitted to Shipping Line for BL release.",
      resolutionStatus: "Override Confirmed", // Pending, Resolved, Override Confirmed
      reviewedBy: "Rajesh B. Patel",
      reviewedAt: "2026-02-18 16:45"
    },
    {
      shipmentNumber: "SHP-1469",
      fieldKey: "hsnCode",
      fieldName: "HSN Code",
      invoiceValue: "68022310 (Granite Slabs)",
      shippingBillValue: "68022310 (Item 1)",
      blValue: "68022310 (Manifested)",
      status: "MATCH",
      reviewNotes: "All 3 documents align with 8-digit Customs tariff classification.",
      resolutionStatus: "Resolved",
      reviewedBy: "Pooja Sharma",
      reviewedAt: "2026-02-18 14:00"
    },
    {
      shipmentNumber: "SHP-1469",
      fieldKey: "grossWeight",
      fieldName: "Cargo Gross Weight",
      invoiceValue: "26,500 KGS",
      shippingBillValue: "26,500 KGS",
      blValue: "26,500 KGS",
      status: "MATCH",
      reviewNotes: "Weighbridge slip matches certified tare + cargo gross weight.",
      resolutionStatus: "Resolved",
      reviewedBy: "Vikramaditya Rathore",
      reviewedAt: "2026-02-18 14:15"
    },
    {
      shipmentNumber: "SHP-1469",
      fieldKey: "packageCount",
      fieldName: "Total Package Count",
      invoiceValue: "226 Wooden Bundles",
      shippingBillValue: "226 Pkgs (INVITEMCONT)",
      blValue: "226 Wooden Bundles",
      status: "MATCH",
      reviewNotes: "Stuffing count physically verified at CFS.",
      resolutionStatus: "Resolved",
      reviewedBy: "Pooja Sharma",
      reviewedAt: "2026-02-18 14:20"
    },
    {
      shipmentNumber: "SHP-1469",
      fieldKey: "containerNumber",
      fieldName: "Container Number",
      invoiceValue: "TCLU7789571",
      shippingBillValue: "TCLU7789571 (Part G)",
      blValue: "TCLU7789571",
      status: "MATCH",
      reviewNotes: "Container matches customs seal 14697.",
      resolutionStatus: "Resolved",
      reviewedBy: "Vikramaditya Rathore",
      reviewedAt: "2026-02-18 14:25"
    },
    {
      shipmentNumber: "SHP-1469",
      fieldKey: "fobCifValuation",
      fieldName: "Customs Valuation Term",
      invoiceValue: "Total CIF: $16,425.27 (FOB $15,425.27)",
      shippingBillValue: "FOB INR 14,69,656.66 (Exch 89.475)",
      blValue: "Freight Prepaid (CIF)",
      status: "WARNING",
      reviewNotes: "FOB INR in Shipping Bill reflects customs statutory valuation; difference of freight $900 & insurance $100 correctly accounted in SB Part C.",
      resolutionStatus: "Override Confirmed",
      reviewedBy: "Vikramaditya Rathore",
      reviewedAt: "2026-02-18 16:50"
    }
  ],

  // =========================================================================
  // DOCUMENT VERSIONS & AUDIT LOGS (IMMUTABLE LOGS)
  // =========================================================================
  documentVersions: [
    {
      id: "VER-1469-01",
      shipmentNumber: "SHP-1469",
      docType: "Commercial Invoice",
      versionNumber: "v1.0 (Original)",
      variant: "Original",
      sourceFile: "1469 INVOICE ORIGINAL.pdf",
      checksumSha256: "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855",
      isImmutable: true,
      createdAt: "2026-02-14 10:15",
      generatedBy: "Pooja Sharma"
    },
    {
      id: "VER-1469-02",
      shipmentNumber: "SHP-1469",
      docType: "Commercial Invoice",
      versionNumber: "v1.1 (Edited)",
      variant: "Edited",
      sourceFile: "1469 INV EDITED.pdf",
      checksumSha256: "7d793037a0760186574b0282f2f435e70d73a46fc5f97a3a30285dddef841fb4",
      isImmutable: false,
      createdAt: "2026-02-15 11:20",
      generatedBy: "Rajesh B. Patel"
    },
    {
      id: "VER-1469-03",
      shipmentNumber: "SHP-1469",
      docType: "Bill of Lading",
      versionNumber: "v1.0 (Original)",
      variant: "Original",
      sourceFile: "1469 BL.pdf",
      checksumSha256: "8f480329ac61abb40f024f97ec323ccd256d7fa30f06a7f068560350be438b51",
      isImmutable: true,
      createdAt: "2026-02-18 12:00",
      generatedBy: "Pooja Sharma"
    },
    {
      id: "VER-1469-04",
      shipmentNumber: "SHP-1469",
      docType: "Bill of Lading",
      versionNumber: "v1.0-HIDE (Masked)",
      variant: "HIDE",
      sourceFile: "BL HIDE.pdf",
      checksumSha256: "a158b093f7c4618721c56ad4a02891d4e138805f1571249b67272895690b919d",
      isImmutable: true,
      createdAt: "2026-02-18 12:15",
      generatedBy: "Pooja Sharma"
    }
  ],

  auditLogs: [
    {
      id: "LOG-01",
      shipmentNumber: "SHP-1469",
      user: "Pooja Sharma (DocOfficer)",
      entity: "Shipment",
      action: "Created New Shipment SHP-1469",
      oldValue: "N/A",
      newValue: "Shipment SHP-1469 initialized with Exporter Reliance Granito & Buyer CJ Stone",
      timestamp: "2026-02-14 09:30"
    },
    {
      id: "LOG-02",
      shipmentNumber: "SHP-1469",
      user: "Pooja Sharma (DocOfficer)",
      entity: "Commercial Invoice",
      action: "Generated Original Invoice v1.0",
      oldValue: "Draft",
      newValue: "Invoice 1469/25-26 saved ($16,425.27 USD)",
      timestamp: "2026-02-14 10:15"
    },
    {
      id: "LOG-03",
      shipmentNumber: "SHP-1469",
      user: "Rajesh B. Patel (Admin)",
      entity: "Commercial Invoice",
      action: "Modified Consignee Destination Address",
      oldValue: "No 10, Nghe Tinh, Da Nang, Vietnam",
      newValue: "Dong Hai Ward, Hai Phong City, Vietnam (Version v1.1 created)",
      timestamp: "2026-02-15 11:20"
    },
    {
      id: "LOG-04",
      shipmentNumber: "SHP-1469",
      user: "Vikramaditya Rathore (CustomsReviewer)",
      entity: "Shipping Bill",
      action: "Imported 6-Page Shipping Bill SB-9716398",
      oldValue: "Pending Customs",
      newValue: "Port INMAA1 Chennai, LEO 17-FEB-26 Recorded",
      timestamp: "2026-02-17 18:00"
    },
    {
      id: "LOG-05",
      shipmentNumber: "SHP-1469",
      user: "Vikramaditya Rathore (CustomsReviewer)",
      entity: "Cross-Document Validation",
      action: "Executed 3-Way Discrepancy Analysis",
      oldValue: "Unchecked",
      newValue: "Flagged 1 Mismatch (Buyer Address) and 1 Valuation Warning",
      timestamp: "2026-02-18 14:30"
    },
    {
      id: "LOG-06",
      shipmentNumber: "SHP-1469",
      user: "Rajesh B. Patel (Admin)",
      entity: "Cross-Document Validation",
      action: "Confirmed Manual Override for Buyer Address Mismatch",
      oldValue: "Mismatch Pending",
      newValue: "Resolution recorded: Buyer requested Hai Phong discharge for direct factory delivery",
      timestamp: "2026-02-18 16:50"
    }
  ]
};

// =========================================================================
// LOCALSTORAGE INITIALIZATION & PERSISTENCE
// =========================================================================
function getExportData() {
  const stored = localStorage.getItem("reliance_granito_edms_data");
  if (stored) {
    try {
      const parsed = JSON.parse(stored);
      if (parsed && Array.isArray(parsed.shipments) && parsed.shipments.length > 0 && Array.isArray(parsed.commercialInvoices) && Array.isArray(parsed.parties)) {
        return parsed;
      }
    } catch (e) {
      console.warn("Could not parse stored EDMS data, falling back to seed:", e);
    }
  }
  saveExportData(RelianceEDMSSeed);
  return RelianceEDMSSeed;
}

function saveExportData(data) {
  localStorage.setItem("reliance_granito_edms_data", JSON.stringify(data));
}

function resetDemoData() {
  localStorage.setItem("reliance_granito_edms_data", JSON.stringify(RelianceEDMSSeed));
  if (typeof showToast === "function") {
    showToast("EDMS demo data restored to initial state!", "success");
  }
  setTimeout(() => window.location.reload(), 500);
}
