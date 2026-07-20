/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

((document) =>
{
    document.getElementById("submitStep")?.addEventListener("click", () =>
    {
        document.forms["publicfolderForm"].submit();
    });
})(document);