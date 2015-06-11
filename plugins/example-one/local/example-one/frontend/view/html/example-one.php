<?php if (is_array($this->result_set)): ?>
    <table>
        <thead>
            <tr>
                <?php if (is_array($this->result_set[0])): ?>
                    <?php foreach ($this->result_set[0] as $key => $value): ?>
                            <th style="text-align: center;">
                                <?php echo ucwords(strtolower(preg_replace('/(-|_)/', ' ', $key)));?>
                            </th>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->result_set as $index => $array): ?>
                <tr>
                    <?php foreach ($array as $key => $value): ?>
                        <td style="text-align: center;">
                            <?php echo $value.':'.gettype($value); ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>