// Header guard
#ifndef BLAKE2B_H
#define BLAKE2B_H


// Function prototypes

// Compute
extern "C" __attribute__((dllexport, visibility("default"))) bool compute(uint8_t *result, const size_t resultSize, const char *input, const size_t inputSize, const char *key, const size_t keySize);


#endif
