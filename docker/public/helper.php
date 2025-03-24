<?php

/**
 * Echoes a string wrapped in <p> tags.
 *
 * @param string $text The text to be wrapped in <p> tags.
 */
function echoParagraph($text)
{
  echo "<p>" . htmlspecialchars($text) . "</p>";
}
