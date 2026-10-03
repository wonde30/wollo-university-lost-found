-- ==============================================================================
-- Wollo University Lost & Found Item Tracking Portal
-- Database Relational Integrity Forensic Audit Script
-- Database: wollo_lost_found_normalized (MySQL 8.0 InnoDB)
-- Date: 2026-10-01
-- ==============================================================================

USE wollo_lost_found_normalized;

SELECT '--- 1. Claims referencing non-existent items ---' AS Check_Description;
SELECT count(*) AS orphaned_claims 
FROM claims c 
LEFT JOIN items i ON c.item_id = i.id 
WHERE i.id IS NULL;

SELECT '--- 2. Returns referencing non-existent claims ---' AS Check_Description;
SELECT count(*) AS orphaned_returns 
FROM returns r 
LEFT JOIN claims c ON r.claim_id = c.id 
WHERE c.id IS NULL;

SELECT '--- 3. Custody events referencing non-existent items ---' AS Check_Description;
SELECT count(*) AS orphaned_custody_events 
FROM custody_events ce 
LEFT JOIN items i ON ce.item_id = i.id 
WHERE i.id IS NULL;

SELECT '--- 4. Match suggestions referencing non-existent items ---' AS Check_Description;
SELECT count(*) AS orphaned_matches 
FROM match_suggestions ms 
LEFT JOIN items i ON ms.lost_item_id = i.id 
WHERE i.id IS NULL;

SELECT '--- 5. Items referencing non-existent categories ---' AS Check_Description;
SELECT count(*) AS orphaned_item_categories 
FROM items i 
LEFT JOIN categories c ON i.category_id = c.id 
WHERE c.id IS NULL;

SELECT '--- 6. Items referencing non-existent campuses ---' AS Check_Description;
SELECT count(*) AS orphaned_item_campuses 
FROM items i 
LEFT JOIN campuses cp ON i.campus_id = cp.id 
WHERE cp.id IS NULL;

SELECT '--- 7. Permission-Role bindings referencing non-existent roles ---' AS Check_Description;
SELECT count(*) AS orphaned_role_bindings 
FROM permission_role pr 
LEFT JOIN roles r ON pr.role_id = r.id 
WHERE r.id IS NULL;
