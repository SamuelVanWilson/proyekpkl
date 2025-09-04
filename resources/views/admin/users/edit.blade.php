@extends('layouts.admin')
@error('role')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>


{{-- Status Aktif --}}
<div class="flex items-center">
<input type="hidden" name="is_active" value="0">
<input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
class="h-4 w-4 rounded border-gray-300">
<label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
@error('is_active')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>


{{-- Paket Langganan (opsional) --}}
<div>
<label for="subscription_plan" class="block text-sm font-medium text-gray-700">Paket Langganan</label>
<select name="subscription_plan" id="subscription_plan" class="mt-1 block w-full rounded-lg border @error('subscription_plan') border-red-500 @else border-gray-300 @enderror">
<option value="" {{ old('subscription_plan', $user->subscription_plan) === null ? 'selected' : '' }}>— Tidak Ada —</option>
<option value="mingguan" {{ old('subscription_plan', $user->subscription_plan) === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
<option value="bulanan" {{ old('subscription_plan', $user->subscription_plan) === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
<option value="3_bulan" {{ old('subscription_plan', $user->subscription_plan) === '3_bulan' ? 'selected' : '' }}>3 Bulan</option>
</select>
@error('subscription_plan')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>


{{-- Kadaluarsa Langganan (opsional) --}}
<div>
<label for="subscription_expires_at" class="block text-sm font-medium text-gray-700">Kadaluarsa Langganan</label>
<input type="datetime-local" name="subscription_expires_at" id="subscription_expires_at"
value="{{ old('subscription_expires_at', optional($user->subscription_expires_at)->format('Y-m-d\\TH:i')) }}"
class="mt-1 block w-full rounded-lg border @error('subscription_expires_at') border-red-500 @else border-gray-300 @enderror">
@error('subscription_expires_at')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>


{{-- Kadaluarsa Penawaran (opsional) --}}
<div>
<label for="offer_expires_at" class="block text-sm font-medium text-gray-700">Kadaluarsa Penawaran</label>
<input type="datetime-local" name="offer_expires_at" id="offer_expires_at"
value="{{ old('offer_expires_at', optional($user->offer_expires_at)->format('Y-m-d\\TH:i')) }}"
class="mt-1 block w-full rounded-lg border @error('offer_expires_at') border-red-500 @else border-gray-300 @enderror">
@error('offer_expires_at')
<p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
</div>
</div>


<div class="mt-6 flex items-center justify-end gap-3">
<a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border text-gray-700">Batal</a>
<button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan Perubahan</button>
</div>
</form>
</div>
@endsection