<h2>Data Mahasiswa</h2>
<a href="<?php echo $this->homeAddress('/add-new-student'); ?>">Tambah Mahasiswa Baru</a>
<br/>
<br/>
<table class="data-table">
    <thead class="data-table-th">
        <td class="data-table-td">ID</td>
        <td class="data-table-td">Nama</td>
        <td class="data-table-td">Alamat</td>
        <td class="data-table-td">No. Telepon</td>
    </thead>
    <tbody>
    <?php foreach ($this->data('all_students') as $student) { ?>
        <tr class="data-table-tr">
            <td class="data-table-td"><?php echo $student['id'] ?></td>
            <td class="data-table-td"><?php echo $student['name'] ?></td>
            <td class="data-table-td"><?php echo $student['address'] ?></td>
            <td class="data-table-td"><?php echo $student['phone_number'] ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
