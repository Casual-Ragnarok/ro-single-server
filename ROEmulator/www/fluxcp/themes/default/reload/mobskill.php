<?php if (!defined('FLUX_ROOT')) exit; ?>
<h2><?php echo htmlspecialchars(Flux::message('ReloadMobSkillsHeading')) ?></h2>
<?php if (!empty($errorMessage)): ?>
<p class="red"><?php echo htmlspecialchars($errorMessage) ?></p>
<?php else: ?>
<p><?php echo htmlspecialchars(sprintf(Flux::message('ReloadMobSkillsInfo'), number_format(filesize($mobDB)))) ?></p>
<?php endif ?>