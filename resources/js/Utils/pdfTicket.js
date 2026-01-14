import pdfMake from 'pdfmake/build/pdfmake';
import * as pdfFonts from 'pdfmake/build/vfs_fonts';

// Initialize pdfMake with fonts
pdfMake.vfs = pdfFonts.pdfMake ? pdfFonts.pdfMake.vfs : pdfFonts.vfs;

/**
 * Generate a PDF ticket using pdfmake (client-side)
 * @param {Object} booking - The booking object with all necessary data
 * @returns {Promise<void>}
 */
export async function generateTicketPdf(booking) {
    // Format date helper
    const formatDate = (dateStr) => {
        const date = new Date(dateStr);
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    };

    // Format currency helper
    const formatCurrency = (amount) => {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    };

    // Generate QR code data
    const qrData = JSON.stringify({
        code: booking.ticket?.ticket_code || booking.order_number,
        name: booking.leader_name,
        order: booking.order_number,
        date: booking.visit_date?.split('T')[0] || booking.visit_date,
    });

    // Build visitor summary
    const visitorRows = [];
    if (booking.total_adults > 0) {
        visitorRows.push({ label: 'Dewasa', count: booking.total_adults });
    }
    if (booking.total_children > 0) {
        visitorRows.push({ label: 'Anak-anak', count: booking.total_children });
    }
    if (booking.total_seniors > 0) {
        visitorRows.push({ label: 'Pelajar', count: booking.total_seniors });
    }
    visitorRows.push({ label: 'Total Pengunjung', count: booking.total_visitors });

    // PDF document definition
    const docDefinition = {
        pageSize: 'A4',
        pageMargins: [40, 40, 40, 40],
        content: [
            // Header
            {
                table: {
                    widths: ['*'],
                    body: [[{
                        fillColor: '#00796b',
                        margin: [20, 15, 20, 15],
                        stack: [
                            {
                                text: 'TNLL EXPLORE',
                                style: 'headerTitle',
                                alignment: 'center'
                            },
                            {
                                text: 'E-Ticket Taman Nasional Lore Lindu',
                                style: 'headerSubtitle',
                                alignment: 'center'
                            }
                        ]
                    }]]
                },
                layout: 'noBorders'
            },
            { text: '', margin: [0, 15, 0, 0] },

            // Main Content - Two Columns
            {
                columns: [
                    // Left Column - Info
                    {
                        width: '55%',
                        stack: [
                            // Destination
                            {
                                stack: [
                                    { text: 'DESTINASI', style: 'infoLabel' },
                                    { text: booking.destination?.name || 'N/A', style: 'destinationName' }
                                ],
                                margin: [0, 0, 0, 15]
                            },
                            // Visit Date
                            {
                                stack: [
                                    { text: 'TANGGAL KUNJUNGAN', style: 'infoLabel' },
                                    { text: formatDate(booking.visit_date), style: 'infoValue' }
                                ],
                                margin: [0, 0, 0, 15]
                            },
                            // Order Number
                            {
                                stack: [
                                    { text: 'NOMOR PESANAN', style: 'infoLabel' },
                                    {
                                        columns: [
                                            {
                                                text: '#' + booking.order_number,
                                                style: 'orderBox',
                                                width: 'auto'
                                            },
                                            {
                                                text: 'LUNAS',
                                                style: 'statusBadge',
                                                width: 'auto',
                                                margin: [10, 0, 0, 0]
                                            }
                                        ]
                                    }
                                ],
                                margin: [0, 0, 0, 15]
                            },
                            // Leader Name
                            {
                                stack: [
                                    { text: 'NAMA PEMESAN', style: 'infoLabel' },
                                    { text: booking.leader_name, style: 'infoValue' }
                                ],
                                margin: [0, 0, 0, 15]
                            },
                            // Total Amount
                            {
                                stack: [
                                    { text: 'TOTAL PEMBAYARAN', style: 'infoLabel' },
                                    { text: formatCurrency(booking.total_amount), style: 'infoValue' }
                                ],
                                margin: [0, 0, 0, 15]
                            }
                        ]
                    },
                    // Right Column - QR Code
                    {
                        width: '45%',
                        stack: [
                            {
                                qr: qrData,
                                fit: 130,
                                alignment: 'center',
                                margin: [0, 10, 0, 10]
                            },
                            booking.ticket?.ticket_code ? {
                                text: booking.ticket.ticket_code,
                                style: 'ticketCode',
                                alignment: 'center',
                                margin: [0, 10, 0, 5]
                            } : null,
                            {
                                text: 'Scan QR Code di pintu masuk',
                                style: 'scanInfo',
                                alignment: 'center'
                            }
                        ].filter(Boolean)
                    }
                ]
            },
            { text: '', margin: [0, 15, 0, 0] },

            // Visitors Section
            {
                table: {
                    widths: Array(visitorRows.length).fill('*'),
                    body: [
                        visitorRows.map(v => ({
                            stack: [
                                { text: v.count.toString(), style: 'visitorCount', alignment: 'center' },
                                { text: v.label, style: 'visitorLabel', alignment: 'center' }
                            ],
                            margin: [5, 10, 5, 10]
                        }))
                    ]
                },
                layout: {
                    fillColor: '#f8f9fa',
                    hLineWidth: () => 0,
                    vLineWidth: () => 0
                }
            },
            { text: '', margin: [0, 15, 0, 0] },

            // Footer
            {
                table: {
                    widths: ['*'],
                    body: [[{
                        fillColor: '#f5f5f5',
                        margin: [20, 10, 20, 10],
                        stack: [
                            {
                                text: 'E-Ticket ini adalah bukti pembayaran yang sah',
                                style: 'footerBold',
                                alignment: 'center'
                            },
                            {
                                text: `Dicetak: ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })} | © ${new Date().getFullYear()} TNLL Explore`,
                                style: 'footerText',
                                alignment: 'center'
                            }
                        ]
                    }]]
                },
                layout: 'noBorders'
            }
        ],
        styles: {
            headerTitle: {
                fontSize: 22,
                bold: true,
                color: '#ffffff',
                letterSpacing: 1
            },
            headerSubtitle: {
                fontSize: 11,
                color: '#e0f2f1'
            },
            infoLabel: {
                fontSize: 9,
                color: '#666666',
                bold: true
            },
            infoValue: {
                fontSize: 13,
                bold: true,
                color: '#1a1a1a'
            },
            destinationName: {
                fontSize: 18,
                bold: true,
                color: '#00796b'
            },
            orderBox: {
                fontSize: 12,
                bold: true,
                color: '#333333',
                background: '#f5f5f5'
            },
            statusBadge: {
                fontSize: 10,
                bold: true,
                color: '#ffffff',
                background: '#00796b'
            },
            ticketCode: {
                fontSize: 13,
                bold: true,
                color: '#00796b',
                background: '#e0f2f1'
            },
            scanInfo: {
                fontSize: 9,
                color: '#888888'
            },
            visitorCount: {
                fontSize: 20,
                bold: true,
                color: '#00796b'
            },
            visitorLabel: {
                fontSize: 9,
                color: '#666666'
            },
            footerBold: {
                fontSize: 9,
                bold: true,
                color: '#888888'
            },
            footerText: {
                fontSize: 9,
                color: '#888888'
            }
        },
        defaultStyle: {
            font: 'Roboto'
        }
    };

    // Generate and download PDF
    const filename = `E-Ticket-TNLL-${booking.order_number}.pdf`;
    pdfMake.createPdf(docDefinition).download(filename);
}

/**
 * Open PDF in new window for preview
 * @param {Object} booking - The booking object
 */
export function previewTicketPdf(booking) {
    // Same logic as generateTicketPdf but opens in new window
    // For now, just call download
    generateTicketPdf(booking);
}
