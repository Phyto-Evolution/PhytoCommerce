#!/bin/bash
# WordPress Activity Scanner
# Run this as root on the server: bash check-wp-activity.sh

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "======================================================"
echo " WordPress Activity Scanner"
echo " $(date)"
echo "======================================================"

# 1. Find any WordPress installations on disk
echo -e "\n${YELLOW}[1] WordPress installations on disk${NC}"
find /home /var/www /srv 2>/dev/null -name "wp-config.php" -o -name "wp-login.php" 2>/dev/null | head -20
WP_COUNT=$(find /home /var/www /srv 2>/dev/null -name "wp-config.php" 2>/dev/null | wc -l)
echo "Found: $WP_COUNT wp-config.php files"

# 2. Check nginx access logs for WP probes
echo -e "\n${YELLOW}[2] Nginx logs — WordPress probe attempts${NC}"
for log in /var/log/nginx/access.log /var/log/nginx/*.log; do
    [ -f "$log" ] || continue
    echo "  Checking: $log"
    grep -iE "wp-login|wp-admin|xmlrpc|wp-content|wp-includes|wordpress" "$log" 2>/dev/null | tail -20
done

# 3. Check Apache/vhost access logs
echo -e "\n${YELLOW}[3] Apache / vhost access logs — WordPress probes${NC}"
for log in /home/*/web/*/log/access.log /var/log/apache2/access.log; do
    [ -f "$log" ] || continue
    HITS=$(grep -icE "wp-login|xmlrpc|wp-admin" "$log" 2>/dev/null)
    if [ "$HITS" -gt 0 ]; then
        echo -e "  ${RED}HITS ($HITS): $log${NC}"
        grep -iE "wp-login|xmlrpc|wp-admin" "$log" 2>/dev/null | tail -10
    else
        echo -e "  ${GREEN}Clean (0 hits): $log${NC}"
    fi
done

# 4. Check HestiaCP site logs (catches all vhosts)
echo -e "\n${YELLOW}[4] HestiaCP vhost logs — WordPress probes${NC}"
for log in /home/*/web/*/log/error.log; do
    [ -f "$log" ] || continue
    HITS=$(grep -icE "wp-login|xmlrpc|wp-admin|wordpress" "$log" 2>/dev/null)
    [ "$HITS" -gt 0 ] && echo -e "  ${RED}ERROR LOG HITS ($HITS): $log${NC}"
done

# 5. Check for wp-login brute force in last 24h
echo -e "\n${YELLOW}[5] wp-login.php POST attempts (last 24h)${NC}"
find /home/*/web/*/log/ /var/log/nginx/ /var/log/apache2/ 2>/dev/null -name "*.log" \
  -newer /tmp/.last24h_check 2>/dev/null | while read log; do
    grep -iE "POST.*wp-login" "$log" 2>/dev/null | tail -5
done
# Also check all logs regardless of age
grep -rihE "POST.*wp-login" /home/*/web/*/log/ /var/log/nginx/ 2>/dev/null | tail -20

# 6. xmlrpc.php attacks (common WP vector)
echo -e "\n${YELLOW}[6] xmlrpc.php attack attempts${NC}"
grep -rihE "xmlrpc\.php" /home/*/web/*/log/ /var/log/nginx/ /var/log/apache2/ 2>/dev/null | \
  grep -v ".gz" | tail -20

# 7. Check if any WP files were recently modified (last 7 days)
echo -e "\n${YELLOW}[7] Recently modified WordPress files (last 7 days)${NC}"
find /home /var/www 2>/dev/null -name "*.php" -newer /tmp/.wp_check_ref \
  -path "*/wp-*" 2>/dev/null | head -20
# Create reference file for next run
touch -d "7 days ago" /tmp/.wp_check_ref 2>/dev/null || true
find /home /var/www 2>/dev/null \( -name "wp-config.php" -o -name "functions.php" \) \
  -newer /tmp/.wp_check_ref 2>/dev/null | head -20

# 8. Check for suspicious PHP files (webshells) in web roots
echo -e "\n${YELLOW}[8] Suspicious PHP files (potential webshells)${NC}"
for webroot in /home/*/web/*/public_html; do
    [ -d "$webroot" ] || continue
    find "$webroot" -name "*.php" -newer /tmp/.wp_check_ref 2>/dev/null | while read f; do
        grep -lE "eval\(base64|system\(\$|exec\(\$|passthru\(\$|shell_exec" "$f" 2>/dev/null && \
          echo -e "  ${RED}SUSPICIOUS: $f${NC}"
    done
done

# 9. Summary
echo -e "\n======================================================"
echo -e "${YELLOW}SUMMARY${NC}"
echo -e "======================================================"
echo "WordPress installs found: $WP_COUNT"
TOTAL_WP_HITS=$(grep -rihcE "wp-login|xmlrpc|wp-admin" \
  /home/*/web/*/log/ /var/log/nginx/ /var/log/apache2/ 2>/dev/null | \
  awk -F: '{sum+=$2} END{print sum+0}')
echo "Total WP probe hits in logs: $TOTAL_WP_HITS"

if [ "$TOTAL_WP_HITS" -gt 0 ]; then
    echo -e "${RED}→ WordPress probe activity detected in logs${NC}"
    echo "  These are HTTP-level attacks — check if any resulted in 200 responses"
    echo "  Run: grep -E 'wp-login|xmlrpc' /var/log/nginx/access.log | grep ' 200 '"
else
    echo -e "${GREEN}→ No WordPress probe activity found${NC}"
fi
echo ""
