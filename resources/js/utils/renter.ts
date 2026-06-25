export function renterFullName(renter: {
    last_name?: string | null;
    name?: string | null;
    middle_name?: string | null;
}): string {
    return [renter.last_name, renter.name, renter.middle_name]
        .filter(Boolean)
        .join(' ');
}
