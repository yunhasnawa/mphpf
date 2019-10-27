<form method="post" action="<?php $this->homeAddress('/add-new-student'); ?>">
    <label for="txt_name">Nama:</label>
    <br/>
    <input style="margin-bottom: 16px; width: 20%" id="txt_name" type="text" placeholder="Nama lengkap mahasiswa" name="name"/>
    <br />
    <label for="txa_address">Alamat:</label>
    <br/>
    <textarea style="margin-bottom: 16px; width: 20%; height: 60px;" id="txa_address" type="text" placeholder="Alamat lengkap mahasiswa" name="address"></textarea>
    <br/>
    <label for="txt_phone_number">No. Telepon:</label>
    <br/>
    <input style="margin-bottom: 16px; width: 20%" type="txt_phone_number" placeholder="No. Telepon yang aktif" name="phone_number"/>
    <br/>
    <input type="submit" name="submit" value="Simpan" />
</form>
